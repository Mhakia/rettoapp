<?php

namespace App\Jobs;

use App\Mail\BulletinMail;
use App\Models\ChallengeCompletion;
use App\Models\Institution;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class SendInstitutionBulletinsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Institution $institution,
        public CarbonInterface $periodStart,
        public CarbonInterface $periodEnd,
    ) {}

    public function handle(): void
    {
        $studentUserIds = $this->institution->memberships()
            ->where('status', 'active')
            ->pluck('user_id');

        $guardians = User::role('guardian')
            ->whereHas('guardianStudents', fn ($query) => $query->whereIn('students.user_id', $studentUserIds))
            ->get();

        foreach ($guardians as $guardian) {
            $this->sendGuardianBulletin($guardian, $studentUserIds);
        }
    }

    protected function sendGuardianBulletin(User $guardian, $studentUserIds): void
    {
        $completions = ChallengeCompletion::with(['challenge', 'user'])
            ->where('status', 'verified')
            ->whereIn('user_id', $guardian->guardianStudents()
                ->whereIn('students.user_id', $studentUserIds)
                ->pluck('students.user_id'))
            ->whereBetween('verified_at', [$this->periodStart, $this->periodEnd])
            ->get();

        if ($completions->isEmpty()) {
            return;
        }

        $html = view('reports.bulletin', [
            'institution' => $this->institution,
            'guardian' => $guardian,
            'completions' => $completions,
            'periodStart' => $this->periodStart,
            'periodEnd' => $this->periodEnd,
        ])->render();

        $pdf = Browsershot::html($html)->format('Letter')->pdf();

        $path = "bulletins/{$this->institution->id}/{$guardian->id}-{$this->periodStart->format('Ymd')}.pdf";
        Storage::disk('s3')->put($path, $pdf);

        Mail::to($guardian->email)->send(new BulletinMail($this->institution, $this->periodStart, $this->periodEnd, $path));

        Report::create([
            'institution_id' => $this->institution->id,
            'type' => 'bulletin',
            'recipient_id' => $guardian->id,
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'file_path' => $path,
            'generated_at' => now(),
        ]);
    }
}

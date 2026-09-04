<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ClassSession extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'group_id',
        'challenge_id',
        'created_by',
        'code',
        'expires_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('closed_at')->where('expires_at', '>', now());
    }

    public function close(): void
    {
        $this->update(['closed_at' => now()]);
    }

    /**
     * A short code, unique among currently open sessions, drawn from an alphabet
     * without visually ambiguous characters (0/O, 1/I).
     */
    public static function generateCode(): string
    {
        static::whereNull('closed_at')->where('expires_at', '<=', now())->update(['closed_at' => now()]);

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('code', $code)->active()->exists());

        return $code;
    }
}

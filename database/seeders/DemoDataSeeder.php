<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use App\Models\Group;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed a realistic demo dataset: platform staff, institutions, portable actors and a challenge catalog.
     */
    public function run(): void
    {
        $pedagogue = User::role('pedagogue')->firstOrFail();

        $institutions = Institution::factory()->count(3)->create();

        $groupsByInstitution = [];
        $studentsByInstitution = [];

        foreach ($institutions as $institution) {
            $admin = User::factory()->create([
                'name' => fake()->name(),
                'institution_id' => $institution->id,
            ]);
            $admin->assignRole('institution_admin');

            $groups = collect(['Transición', '1°A', '2°A'])
                ->map(fn ($name) => Group::create([
                    'institution_id' => $institution->id,
                    'name' => $name,
                ]));
            $groupsByInstitution[$institution->id] = $groups;

            foreach (range(1, 3) as $i) {
                $teacher = $this->makeActor('teacher', 'cedula_ciudadania');
                $membership = InstitutionMembership::create([
                    'user_id' => $teacher->id,
                    'institution_id' => $institution->id,
                    'status' => 'active',
                    'started_at' => now()->subMonths(6),
                ]);
                $teacher->teacherGroups()->attach($groups->random(2)->pluck('id'), ['institution_membership_id' => $membership->id]);
            }

            $students = collect();

            foreach ($groups as $group) {
                foreach (range(1, 3) as $i) {
                    $student = $this->makeStudent();
                    InstitutionMembership::create([
                        'user_id' => $student->user_id,
                        'institution_id' => $institution->id,
                        'group_id' => $group->id,
                        'status' => 'active',
                        'started_at' => now()->subMonths(6),
                    ]);
                    $students->push($student);
                }
            }

            $studentsByInstitution[$institution->id] = $students;

            // Regular guardian: one student in this institution.
            $guardian = $this->makeActor('guardian', 'cedula_ciudadania');
            $guardian->guardianStudents()->attach($students->random()->id);
        }

        // Guardian with siblings in the same institution.
        $siblingGuardian = $this->makeActor('guardian', 'cedula_ciudadania');
        $siblingGuardian->guardianStudents()->attach(
            $studentsByInstitution[$institutions[0]->id]->random(2)->pluck('id')
        );

        // Guardian with children in two different institutions.
        $crossInstitutionGuardian = $this->makeActor('guardian', 'cedula_ciudadania');
        $crossInstitutionGuardian->guardianStudents()->attach([
            $studentsByInstitution[$institutions[0]->id]->random()->id,
            $studentsByInstitution[$institutions[1]->id]->random()->id,
        ]);

        // Student with transfer history: two closed memberships, now active in a third institution.
        $transferredStudent = $this->makeStudent();
        InstitutionMembership::create([
            'user_id' => $transferredStudent->user_id,
            'institution_id' => $institutions[0]->id,
            'group_id' => $groupsByInstitution[$institutions[0]->id]->first()->id,
            'status' => 'withdrawn',
            'started_at' => now()->subYear(),
            'ended_at' => now()->subMonths(8),
            'reason' => 'Traslado de ciudad',
        ]);
        InstitutionMembership::create([
            'user_id' => $transferredStudent->user_id,
            'institution_id' => $institutions[1]->id,
            'group_id' => $groupsByInstitution[$institutions[1]->id]->first()->id,
            'status' => 'transferred',
            'started_at' => now()->subMonths(8),
            'ended_at' => now()->subMonths(2),
            'reason' => 'Cambio de colegio',
        ]);
        $currentMembership = InstitutionMembership::create([
            'user_id' => $transferredStudent->user_id,
            'institution_id' => $institutions[2]->id,
            'group_id' => $groupsByInstitution[$institutions[2]->id]->first()->id,
            'status' => 'active',
            'started_at' => now()->subMonths(2),
        ]);
        $studentsByInstitution[$institutions[2]->id]->push($transferredStudent);

        $this->seedChallenges($pedagogue, $institutions, $studentsByInstitution, $currentMembership);
    }

    protected function makeActor(string $role, string $documentType): User
    {
        $user = User::factory()->create([
            'name' => fake()->name(),
            'document_type' => $documentType,
            'document_number' => fake()->unique()->numerify('##########'),
        ]);
        $user->assignRole($role);

        return $user;
    }

    protected function makeStudent(): Student
    {
        $user = User::factory()->create(['name' => fake()->name()]);
        $user->assignRole('student');

        return Student::create([
            'user_id' => $user->id,
            'document_type' => fake()->randomElement(['registro_civil', 'tarjeta_identidad']),
            'document_number' => fake()->unique()->numerify('##########'),
            'birth_date' => fake()->dateTimeBetween('-17 years', '-6 years'),
        ]);
    }

    protected function seedChallenges($pedagogue, $institutions, $studentsByInstitution, InstitutionMembership $transferredStudentMembership): void
    {
        $challenges = collect([
            ['target_role' => 'student', 'category' => 'inclusion', 'difficulty' => 'easy', 'points' => 20],
            ['target_role' => 'student', 'category' => 'socioemotional_wellbeing', 'difficulty' => 'medium', 'points' => 40],
            ['target_role' => 'teacher', 'category' => 'inclusion', 'difficulty' => 'medium', 'points' => 50],
            ['target_role' => 'guardian', 'category' => 'community', 'difficulty' => 'easy', 'points' => 30],
            ['target_role' => 'student', 'category' => 'community', 'difficulty' => 'hard', 'points' => 80],
        ])->map(function (array $attributes) use ($pedagogue) {
            return Challenge::create($attributes + [
                'title' => fake()->sentence(4),
                'description' => fake()->paragraph(),
                'status' => 'published',
                'created_by' => $pedagogue->id,
            ]);
        });

        // Restrict the last challenge to a single institution; the rest stay open to all (no pivot rows).
        $challenges->last()->institutions()->attach($institutions[0]->id);

        $studentChallenges = $challenges->where('target_role', 'student')->values();
        $students = $studentsByInstitution[$institutions[0]->id];

        ChallengeCompletion::create([
            'challenge_id' => $studentChallenges[0]->id,
            'institution_membership_id' => $students[0]->activeMembership->id,
            'user_id' => $students[0]->user_id,
            'status' => 'verified',
            'points_earned' => $studentChallenges[0]->points,
            'submitted_at' => now()->subDays(5),
            'verified_at' => now()->subDays(4),
        ]);

        ChallengeCompletion::create([
            'challenge_id' => $studentChallenges[1]->id,
            'institution_membership_id' => $students[1]->activeMembership->id,
            'user_id' => $students[1]->user_id,
            'status' => 'submitted',
            'submitted_at' => now()->subDay(),
        ]);

        ChallengeCompletion::create([
            'challenge_id' => $studentChallenges[0]->id,
            'institution_membership_id' => $students[2]->activeMembership->id,
            'user_id' => $students[2]->user_id,
            'status' => 'rejected',
            'submitted_at' => now()->subDays(3),
            'verified_at' => now()->subDays(2),
        ]);

        ChallengeCompletion::create([
            'challenge_id' => $studentChallenges[2]->id,
            'institution_membership_id' => $transferredStudentMembership->id,
            'user_id' => $transferredStudentMembership->user_id,
            'status' => 'pending',
        ]);
    }
}

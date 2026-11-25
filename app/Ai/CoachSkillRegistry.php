<?php

namespace App\Ai;

use Illuminate\Support\Facades\File;

final class CoachSkillRegistry
{
    /** @var array<string, string> */
    private const SKILLS = [
        'progression' => 'progression.md',
        'role' => 'role-and-rank.md',
        'hero' => 'hero-analysis.md',
        'perks' => 'perks-and-matchups.md',
    ];

    public function instructions(): string
    {
        $skills = [];

        foreach (self::SKILLS as $name => $file) {
            $skills[] = "### {$name}\n".trim(File::get(resource_path("ai/skills/{$file}")));
        }

        return implode("\n\n", $skills);
    }
}

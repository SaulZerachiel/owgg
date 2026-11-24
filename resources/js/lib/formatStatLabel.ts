export type FormattedStatLabel = {
    label: string;
    qualifier: string | null;
};

const labelOverrides: Record<string, string> = {
    all_damage_done: 'Total Damage Dealt',
    barrier_damage_done: 'Barrier Damage Dealt',
    critical_hit_accuracy: 'Critical Hit Accuracy',
    damage_blocked: 'Damage Blocked',
    damage_done: 'Damage Dealt',
    games_lost: 'Games Lost',
    games_played: 'Games Played',
    games_won: 'Games Won',
    healing_done: 'Healing Done',
    hero_damage_done: 'Hero Damage Dealt',
    scoped_accuracy: 'Scoped Accuracy',
    time_played: 'Time Played',
    weapon_accuracy: 'Weapon Accuracy',
    win_percentage: 'Win Rate',
};

const qualifiers: Record<string, string> = {
    best_in_game: 'Best in Game',
};

export function formatStatLabel(key: string): FormattedStatLabel {
    const normalizedKey = key.trim().toLowerCase();
    const qualifierKey = Object.keys(qualifiers).find((suffix) =>
        normalizedKey.endsWith(`_${suffix}`),
    );
    const labelKey = qualifierKey
        ? normalizedKey.slice(0, -(qualifierKey.length + 1))
        : normalizedKey;

    return {
        label:
            labelOverrides[labelKey] ??
            labelKey
                .split('_')
                .filter(Boolean)
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' '),
        qualifier: qualifierKey ? qualifiers[qualifierKey] : null,
    };
}

<?php

namespace App\Modules;

/**
 * Hooks - Provides hook execution shortcuts for the application
 *
 * Available hooks:
 *
 * Authentication:
 * - user.login              : After successful login (user data)
 * - user.logout             : After logout (user data)
 * - user.registered         : After new user registration (user data)
 *
 * Player Events:
 * - player.created          : After player record created (player data)
 * - player.updated          : After player record updated (player data)
 * - player.approved         : After admin approves registration (player data)
 * - player.rejected         : After admin rejects registration (player data)
 *
 * Team Events:
 * - team.created            : After team created (team data)
 * - team.player_added       : After player added to team (team, player data)
 * - team.player_removed     : After player removed from team (team, player data)
 *
 * Tryout Events:
 * - tryout.registered       : After player registers for tryout (tryout, player data)
 * - tryout.attended         : After attendance marked (tryout, player data)
 *
 * Payment Events:
 * - payment.received        : After payment recorded (payment data)
 * - payment.failed          : After payment fails (payment data)
 *
 * Dashboard Widgets:
 * - dashboard.superuser     : Inject widgets into superuser dashboard
 * - dashboard.admin         : Inject widgets into admin dashboard
 * - dashboard.coach         : Inject widgets into coach dashboard
 * - dashboard.player        : Inject widgets into player dashboard
 *
 * Navigation:
 * - nav.admin.sidebar       : Add items to admin sidebar
 * - nav.coach.sidebar       : Add items to coach sidebar
 * - nav.player.sidebar      : Add items to player sidebar
 */
class Hooks
{
    /**
     * Execute a hook and return results from all handlers
     */
    public static function run(string $hookName, array $context = []): array
    {
        return ModuleManager::getInstance()->executeHook($hookName, $context);
    }

    /**
     * Execute a hook and return combined HTML output
     */
    public static function render(string $hookName, array $context = []): string
    {
        $results = self::run($hookName, $context);
        $html = '';

        foreach ($results as $moduleName => $output) {
            if (is_string($output)) {
                $html .= $output;
            }
        }

        return $html;
    }

    /**
     * Execute a hook and return first non-null result
     */
    public static function filter(string $hookName, mixed $value, array $context = []): mixed
    {
        $context['value'] = $value;
        $results = self::run($hookName, $context);

        foreach ($results as $result) {
            if ($result !== null) {
                return $result;
            }
        }

        return $value;
    }

    /**
     * Check if any module is listening to a hook
     */
    public static function hasListeners(string $hookName): bool
    {
        $manager = ModuleManager::getInstance();
        $results = $manager->executeHook($hookName . '.check', []);
        return !empty($results);
    }
}

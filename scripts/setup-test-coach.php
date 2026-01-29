<?php
/**
 * Setup script to create test data for coach portal testing
 * Creates: league, team, coach user, coach assignment, and assigns players
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Connect to database
$dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'];
$db = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Setting Up Test Coach Data ===\n\n";

try {
    $db->beginTransaction();

    // 1. Create a league if none exists
    echo "1. Creating league...\n";
    $stmt = $db->query("SELECT id FROM leagues LIMIT 1");
    $league = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$league) {
        $db->exec("INSERT INTO leagues (name, season, year, start_date, end_date, status, created_at, updated_at)
                   VALUES ('IVL Travel League', 'Spring', 2026, '2026-03-01', '2026-06-30', 'active', NOW(), NOW())");
        $leagueId = $db->lastInsertId();
        echo "   Created league ID: $leagueId\n";
    } else {
        $leagueId = $league['id'];
        echo "   Using existing league ID: $leagueId\n";
    }

    // 2. Create test teams for different age groups
    echo "\n2. Creating teams...\n";
    $ageGroups = ['10U', '12U', '14U'];
    $teamIds = [];

    foreach ($ageGroups as $ageGroup) {
        // Check if team exists
        $stmt = $db->prepare("SELECT id FROM teams WHERE age_group = ? AND league_id = ?");
        $stmt->execute([$ageGroup, $leagueId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) {
            $stmt = $db->prepare("INSERT INTO teams (league_id, name, age_group, max_players, status, created_at, updated_at)
                                  VALUES (?, ?, ?, 15, 'active', NOW(), NOW())");
            $stmt->execute([$leagueId, "IVL {$ageGroup} Thunder", $ageGroup]);
            $teamIds[$ageGroup] = $db->lastInsertId();
            echo "   Created team: IVL {$ageGroup} Thunder (ID: {$teamIds[$ageGroup]})\n";
        } else {
            $teamIds[$ageGroup] = $team['id'];
            echo "   Using existing {$ageGroup} team (ID: {$teamIds[$ageGroup]})\n";
        }
    }

    // 3. Create coach user if none exists
    echo "\n3. Creating coach user...\n";
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'testcoach'");
    $stmt->execute();
    $coachUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coachUser) {
        $password = password_hash('coachpass123', PASSWORD_BCRYPT, ['cost' => 12]);
        $db->exec("INSERT INTO users (username, email, password_hash, role, status, email_verified, created_at, updated_at)
                   VALUES ('testcoach', 'coach@test.com', '$password', 'coach', 'active', 1, NOW(), NOW())");
        $coachUserId = $db->lastInsertId();
        echo "   Created coach user 'testcoach' (ID: $coachUserId)\n";
        echo "   Login: testcoach / coachpass123\n";
    } else {
        $coachUserId = $coachUser['id'];
        echo "   Using existing coach user (ID: $coachUserId)\n";
    }

    // 4. Create coach assignment to 12U team
    echo "\n4. Creating coach assignment...\n";
    $mainTeamId = $teamIds['12U'];

    $stmt = $db->prepare("SELECT id FROM coaches WHERE user_id = ? AND team_id = ?");
    $stmt->execute([$coachUserId, $mainTeamId]);
    $coachAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coachAssignment) {
        $stmt = $db->prepare("INSERT INTO coaches (user_id, team_id, coach_type, created_at, updated_at)
                              VALUES (?, ?, 'head', NOW(), NOW())");
        $stmt->execute([$coachUserId, $mainTeamId]);
        echo "   Assigned coach to 12U team as head coach\n";
    } else {
        echo "   Coach already assigned to team\n";
    }

    // 5. Assign players to team
    echo "\n5. Assigning players to team...\n";

    // Get 12U players
    $stmt = $db->prepare("SELECT id FROM players WHERE age_group = '12U' LIMIT 12");
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $assignedCount = 0;
    $jerseyNumber = 1;

    foreach ($players as $player) {
        // Check if already assigned
        $stmt = $db->prepare("SELECT id FROM team_players WHERE team_id = ? AND player_id = ?");
        $stmt->execute([$mainTeamId, $player['id']]);

        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO team_players (team_id, player_id, jersey_number, status, joined_date, created_at)
                                  VALUES (?, ?, ?, 'active', CURDATE(), NOW())");
            $stmt->execute([$mainTeamId, $player['id'], $jerseyNumber]);
            $assignedCount++;
        }
        $jerseyNumber++;
    }
    echo "   Assigned $assignedCount players to 12U team\n";

    $db->commit();

    echo "\n=== Setup Complete ===\n";
    echo "\nCoach Portal Test Credentials:\n";
    echo "  URL: http://leaguemanager.cw.local/coach/dashboard\n";
    echo "  Username: testcoach\n";
    echo "  Password: coachpass123\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

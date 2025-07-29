<?php
function load_ndjson($filepath) {
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];
    foreach ($lines as $line) {
        $json = json_decode($line, true);
        if ($json !== null) {
            $data[] = $json;
        }
    }
    return $data;
}

// Load NDJSON files
$instances = load_ndjson(__DIR__ . "/data/instances.json");
$missions = load_ndjson(__DIR__ . "/data/missions.json");
$packages = load_ndjson(__DIR__ . "/data/mm_packages.json");

// Get latest mission per server
$latestMissions = [];
foreach ($missions as $mission) {
    $name = $mission["server_name"];
    if (!isset($latestMissions[$name]) || $mission["mission_start"] > $latestMissions[$name]["mission_start"]) {
        $latestMissions[$name] = $mission;
    }
}

// Group mods per server
$mods = [];
foreach ($packages as $pkg) {
    $mods[$pkg["server_name"]][] = $pkg["package_name"] . " v" . $pkg["version"];
}
?>

<?php include("header.php"); ?>
<?php include("nav.php"); ?>

<main>
    <div class="server-boxes">
        <?php foreach ($instances as $server): 
            $name = $server["server_name"];
            $mission = $latestMissions[$name] ?? null;
        ?>
            <div class="server-box">
                <span class="server-icon">🎖️</span>
                <strong><?= htmlspecialchars($server["server_name"]) ?></strong><br><br>

                <?php if ($mission): ?>
                    <div><strong>Mission:</strong> <?= htmlspecialchars($mission["mission_name"]) ?></div>
                    <div><strong>Theatre:</strong> <?= htmlspecialchars($mission["mission_theatre"]) ?></div>
                <?php else: ?>
                    <div><em>No mission info</em></div>
                <?php endif; ?>

                <?php if (!empty($mods[$name])): ?>
                    <div style="margin-top: 10px;">
                        <strong>Mods:</strong>
                        <ul style="text-align: left; padding-left: 20px;">
                            <?php foreach ($mods[$name] as $mod): ?>
                                <li><?= htmlspecialchars($mod) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include("footer.php"); ?>

<main>
    <div class="log-entry-content">
        <h1>No Logfiles available</h1>
        <p><?= __("There isn't anything here yet. Usually this is a good sign, if you have issues, please open an issue in <a target='blank' href='" . $options['repository']['issue_url'] . "'>our repository</a> to get help.", 'gravityscores');?></p>
    </div>


    <!-- Log Entry Container -->
    <ul class="log-entries">
        <?php foreach ($logs as $log): ?>

        <li class="log-entry" data-url="<?= $log['url'] ?>">
            <time> <?= $log['time']; ?></time>
            -
            <date><?= $log['date']; ?></date>
        </li>

        <?php endforeach; ?>
    <ul>
</main>
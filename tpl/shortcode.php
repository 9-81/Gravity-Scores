<?php if ($show_visualization ?? true): ?>

    <div class="gs-chart <?= $visualization_name; ?>" data-id="<?= $shortcode_attributes['id']; ?>" ></div>

<?php endif; ?>

<?php if ($show_buttons ?? true): ?>

    <div class="gs-buttons <?= $visualization_name; ?>" data-id="<?= $shortcode_attributes['id'] ?>" ></div>
    
<?php endif; ?>

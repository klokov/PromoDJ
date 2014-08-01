<h2>Парсинг выполнен</h2>
<h3>Загружено миксов: <?php echo count($tracks['added'])?> </h3>
<ul>
    <?php foreach($tracks['added'] as $track): ?>
    <li><?php echo $track['fname']; ?></li>
    <?php endforeach; ?>
</ul>
<h3>Имелось миксов: <?php echo count($tracks['exist'])?> </h3>
<ul>
    <?php foreach($tracks['exist'] as $track): ?>
        <li><?php echo $track['fname']; ?></li>
    <?php endforeach; ?>
</ul>
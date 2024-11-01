<?php
session_start();
require_once 'db.php';

function getActiveBanner($conn) {
    $sql = "SELECT title, description, image_url, link_url 
            FROM br_news_banner 
            WHERE is_active = TRUE
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}


$banner = getActiveBanner($conn);

?>

<!-- HTML per visualizzare il banner -->
<div class="banner-container mb-3" style="background-image: url('<?php echo $banner['image_url']; ?>');">
    <div class="gradient-overlay"></div>
    <div class="content">
        <?php if ($banner): ?>
            <h1 class="title"><?php echo $banner['title']; ?></h1>
            <p class="description"><?php echo $banner['description']; ?></p>
            <p class="link">
                <a href="<?php echo $banner['link_url']; ?>" class="read-more">Continua a leggere...</a>
            </p>
        <?php else: ?>
            <h1 class="title">Nulla da vedere :(</h1>
            <p class="description">Attualmente il mondo è noioso e non ci sono cose interessanti da guardare.</p>
        <?php endif; ?>
    </div>
</div>

<style>
 .banner-container {
    position: relative;
    padding: 4rem 2rem;
    color: #fff;
    background-size: cover;
    background-position: center right;
    border-radius: 8px;
    text-align: left;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, rgba(0, 0, 0, 1), rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0));
    border-radius: 8px;
}

.content {
    position: relative;
    z-index: 2;
    max-width: 600px;
}

.title {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1.2;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
}

.description {
    font-size: 1.1rem;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
}

.read-more {
    color: #fff;
    font-weight: bold;
    text-decoration: underline;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
}

</style>
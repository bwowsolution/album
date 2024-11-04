<?php
session_start();
require_once 'db.php';

function getNewReleases($conn) {
    $sql = "SELECT 
    bralbum.albumId,
    bralbum.albumName,
    bralbum.releaseDate,
    bralbum.cover,
    GROUP_CONCAT(bartist.artistId SEPARATOR '|') AS artistIds,
    GROUP_CONCAT(bartist.artistName SEPARATOR '|') AS artistNames,
    GROUP_CONCAT(bartist.img SEPARATOR '|') AS artistImages
FROM 
    br_albums bralbum
JOIN 
    br_albums_artists_rel braarel ON bralbum.albumId = braarel.albumId
JOIN 
    br_artists bartist ON braarel.artistId = bartist.artistId
GROUP BY 
    bralbum.albumId, bralbum.albumName, bralbum.releaseDate, bralbum.cover
ORDER BY 
    bralbum.releaseDate DESC
LIMIT 9";

    $result = $conn->query($sql);
    $albums = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }
    }

    return $albums;
}

$new_releases = getNewReleases($conn);
?>

<script>
$(document).ready(function() {
    var currentIndex = 0;
    var screenWidth = window.innerWidth;

    if (screenWidth >= 768) {
        slidesToShow = 3; // Tablet
    } else {
        slidesToShow = 1; // Mobile
    }
    const slideWidth = $('.slide').outerWidth(true);
    const $sliderWrapper = $('.slider-wrapper');
    const totalSlides = $('.slide').length;


    function updateSliderPosition() {
        const offset = -currentIndex * slideWidth;
        $sliderWrapper.css('transform', `translateX(${offset}px)`);
        updateBlurEffect();
        updateButtons();
    }

    function nextSlide() {
        if (currentIndex < totalSlides - slidesToShow) {
            currentIndex++;
        } else {
            currentIndex = 0; // Torna all'inizio per ciclo continuo
        }
        updateSliderPosition();
    }

    function prevSlide() {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = totalSlides - slidesToShow; // Vai all'ultimo gruppo di slide
        }
        updateSliderPosition();
    }

    function updateButtons() {
        $('.slider-button.prev').toggleClass('hidden', currentIndex === 0);
        $('.slider-button.next').toggleClass('hidden', currentIndex >= totalSlides - slidesToShow);
    }

    function updateBlurEffect() {
        $('.slide').removeClass('blurred'); // Rimuovi il blur da tutte le slide
        $('.slide').each(function(index) {
            if (index < currentIndex || index >= currentIndex + slidesToShow) {
                $(this).addClass('blurred'); // Aggiungi il blur alle slide fuori dall'area visibile
            }
        });
    }

    $('.slider-button.next').on('click', nextSlide);
    $('.slider-button.prev').on('click', prevSlide);

    updateButtons();
    updateBlurEffect(); // Inizializza l'effetto blur
});


  </script>

<div class="slider-container">
    <button class="slider-button prev">‹</button>
    <div class="slider-wrapper">
        <?php foreach ($new_releases as $release): ?>
            <div class="slide">
                <div class="card bg-white rounded mb-4 position-relative">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-xl-6 p-4">
                            <img class="bd-placeholder-img rounded" style="width: 100%;height: auto;" src="<?php echo htmlspecialchars($release['cover']); ?>" alt="<?php echo htmlspecialchars($release['albumName']); ?>">
                        </div>
                        <div class="col-md-12 col-xl-6 col-sm-12 p-4 d-flex flex-column position-static">
                            <!-- Titolo dell'album come link -->
                            <label class="d-inline-block mb-1">
                                <a href="album.php?albumId=<?php echo urlencode($release['albumId']); ?>" class="text-decoration-none">
                                    <strong><?php echo htmlspecialchars($release['albumName']); ?></strong>
                                </a>
                            </label>

                            <!-- Artisti come link separati -->
                            <small class="mb-2 text-body-tertiary">
                                by <i>
                                <?php 
                                    // Converti gli artistIds e artistNames in array usando "|" come separatore
                                    $artistIds = explode('|', $release['artistIds']);
                                    $artistNames = explode('|', $release['artistNames']);
                                    
                                    // Itera sugli artisti e crea un link per ciascuno
                                    $artistLinks = [];
                                    foreach ($artistIds as $index => $artistId) {
                                        $artistName = htmlspecialchars($artistNames[$index]);
                                        $artistLinks[] = "<a href='artist.php?artistId=" . urlencode($artistId) . "' class='text-decoration-none'>$artistName</a>";
                                    }
                                    
                                    // Unisci i link con una virgola
                                    echo implode(', ', $artistLinks);
                                ?></i>
                            </small>

                            <!-- Valutazione fittizia e numero di voti -->
                            <h2 class="mb-0">8.4</h2>
                            <div class="text-secondary">934,137 voti</div>
                        </div>
                    </div>
                    <div class="col-auto p-4 border-top w-100">
                        <p class="text-body-tertiary mb-0">Marco e altri 3 amici hanno apprezzato questo elemento.</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="slider-button next">›</button>
</div>



<style>
.slider-container {
    position: relative;
    width: 100%;
    margin: 0 auto;
    padding: 1rem 0;
    overflow: visible; /* Permetti alle frecce di essere visibili */
}

.slider-container a{
  color: #000
}

.slider-wrapper {
    display: flex;
    transition: transform 0.5s ease;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.slide {
    min-width: 33.3333%; /* Mostra 3 elementi per volta */
    box-sizing: border-box;
    padding: 0 10px;
    transition: filter 0.3s ease, opacity 0.3s ease;
}

.slider-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    font-size: 1rem;
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-radius: 50%;
    color: #333;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 10; /* Assicurati che le frecce siano in primo piano */
}

/* Default for larger screens (PC and up) */
.prev {
    left: -2.5rem; /* Position arrow to the left */
}

.next {
    right: -2.5rem; /* Position arrow to the right */
}

.slide.blurred {
    filter: blur(5px); /* Applica l'effetto blur */
    opacity: 0.5; /* Diminuisce leggermente l'opacità */
}

/* Apply mobile adjustments */
@media (max-width: 768px) { /* Adjust breakpoint as needed for mobile */
    .prev {
        left: 1rem; /* Position arrow further left for mobile */
    }

    .next {
        right: 1rem; /* Position arrow further right for mobile */
    }

    .slide {
      min-width: 100%;
      box-sizing: border-box;
      padding: 0 10px;
      transition: filter 0.3s ease, opacity 0.3s ease;
    }
}

</style>


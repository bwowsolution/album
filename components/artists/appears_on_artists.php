<?php
session_start();
require_once 'db.php';

function getAlbumsArtistAppearOn($conn, $artistId) {
    // Preparare la query SQL per recuperare gli album e gli artisti associati
    $sql = "
    SELECT bralbums.albumId, 
       bralbums.albumName, 
       bralbums.releaseDate, 
       bralbums.cover,
       GROUP_CONCAT(DISTINCT bralbartrel.artistId SEPARATOR '|') AS artistIds,
       GROUP_CONCAT(DISTINCT bartists.artistName SEPARATOR '|') AS artistNames
FROM br_albums bralbums
JOIN br_albums_songs_rel bralbsr ON bralbums.albumId = bralbsr.albumId
JOIN br_songs brsongs ON bralbsr.songId = brsongs.songId
JOIN br_artists_songs_rel bralsr ON brsongs.songId = bralsr.songId
JOIN br_albums_artists_rel bralbartrel ON bralbums.albumId = bralbartrel.albumId
JOIN br_artists bartists ON bralbartrel.artistId = bartists.artistId 
WHERE bralsr.artistId = ?
AND bralbums.albumId NOT IN (
    SELECT bralbartrel.albumId
    FROM br_albums_artists_rel bralbartrel
    WHERE bralbartrel.artistId = ?
)
GROUP BY bralbums.albumId, bralbums.albumName, bralbums.releaseDate, bralbums.cover
ORDER BY RAND();

    ";

    // Prepara la query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $artistId, $artistId); // Associa l'artistId alla query

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Controlla se ci sono risultati e restituisci gli album
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Recupera tutti i risultati come array associativo
    } else {
        return null; // Nessun album trovato
    }
}

// Supponiamo che l'artistId sia passato tramite GET
$artistId = isset($_GET['artistId']) ? $_GET['artistId'] : null;
$appearsOn = getAlbumsArtistAppearOn($conn, $artistId);

?>

<script>
$(document).ready(function() {
    var currentIndex = 0;
    const slidesToShow = 3;
    const slideWidth = $('.slide-appears-on').outerWidth(true);
    const $sliderWrapper = $('.slider-wrapper-appears-on');
    const totalSlides = $('.slide-appears-on').length;


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
            currentIndex = totalSlides - slidesToShow; // Vai all'ultimo gruppo di slide-appears-on
        }
        updateSliderPosition();
    }

    function updateButtons() {
        $('.slider-button-appears-on.prev').toggleClass('hidden', currentIndex === 0);
        $('.slider-button-appears-on.next').toggleClass('hidden', currentIndex >= totalSlides - slidesToShow);
    }

    function updateBlurEffect() {
        $('.slide-appears-on').removeClass('blurred'); // Rimuovi il blur da tutte le slide-appears-on
        $('.slide-appears-on').each(function(index) {
            if (index < currentIndex || index >= currentIndex + slidesToShow) {
                $(this).addClass('blurred'); // Aggiungi il blur alle slide-appears-on fuori dall'area visibile
            }
        });
    }

    $('.slider-button-appears-on.next').on('click', nextSlide);
    $('.slider-button-appears-on.prev').on('click', prevSlide);

    updateButtons();
    updateBlurEffect(); // Inizializza l'effetto blur
});


  </script>

<div class="slider-container-appears-on">
    <button class="slider-button-appears-on prev">‹</button>
    <div class="slider-wrapper-appears-on">
        <?php foreach ($appearsOn as $release): ?>
            <div class="slide-appears-on">
                <div class="card bg-white rounded mb-4 position-relative">
                    <div class="row">
                        <div class="col-auto p-4">
                            <img class="bd-placeholder-img rounded" width="150" height="150" src="<?php echo htmlspecialchars($release['cover']); ?>" alt="<?php echo htmlspecialchars($release['albumName']); ?>">
                        </div>
                        <div class="col p-4 ps-0 d-flex flex-column position-static">
                            <!-- Titolo dell'album come link -->
                            <label class="d-inline-block mb-1 fs-5">
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
                        <?php 
                        $dateString = $release["releaseDate"];

                        if(strlen($dateString) > 5){
                            $date = DateTime::createFromFormat('Y-m-d', $dateString);
                            $formattedDate = $date->format('d/m/Y');
                        }else{
                            $formattedDate = $dateString;
                        }
                        
                        
                        ?>

                        <small class="mb-2 text-body-tertiary"><i><?php echo "Data di uscita: " . $formattedDate; ?> </i></small>
                        
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="slider-button-appears-on next">›</button>
</div>



<style>
.slider-container-appears-on {
    position: relative;
    width: 100%;
    margin: 0 auto;
    padding: 1rem 0;
    overflow: visible; /* Permetti alle frecce di essere visibili */
}

.slider-container-appears-on a{
  color: #000
}

.slider-wrapper-appears-on {
    display: flex;
    transition: transform 0.5s ease;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.slide-appears-on {
    min-width: 33.3333%; /* Mostra 3 elementi per volta */
    box-sizing: border-box;
    padding: 0 10px;
    transition: filter 0.3s ease, opacity 0.3s ease;
}

.slider-button-appears-on {
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

.prev {
    left: -2.5rem; /* Posiziona la freccia a sinistra del container */
}

.next {
    right: -2.5rem; /* Posiziona la freccia a destra del container */
}

.slide-appears-on.blurred {
    filter: blur(5px); /* Applica l'effetto blur */
    opacity: 0.5; /* Diminuisce leggermente l'opacità */
}

</style>


<?php
session_start();
require_once 'db.php';

function getAlbum($conn) {
    // Verifica se il parametro 'artistId' è settato e non vuoto
    if (empty($_GET['albumId'])) {
        return null;
    }

    // Prepara la query in modo sicuro per evitare SQL Injection
    $sql = "
    SELECT brsongs.songId, bralbums.cover, bralbums.albumName, bralbums.releaseDate, 
       brsongs.songTitle, 
       GROUP_CONCAT(DISTINCT brartist.artistId SEPARATOR '|') AS albumArtistIds,
       GROUP_CONCAT(DISTINCT brartist.artistName SEPARATOR '|') AS albumArtistNames,
       GROUP_CONCAT(DISTINCT feat.artistId SEPARATOR '|') AS featuredArtistIds,
       GROUP_CONCAT(DISTINCT feat.artistName SEPARATOR '|') AS featuredArtistNames
FROM br_songs brsongs
JOIN br_albums_songs_rel bralsr ON brsongs.songId = bralsr.songId
JOIN br_albums bralbums ON bralsr.albumId = bralbums.albumId
JOIN br_albums_artists_rel bralbartrel ON bralbums.albumId = bralbartrel.albumId
JOIN br_artists brartist ON bralbartrel.artistId = brartist.artistId
LEFT JOIN br_artists_songs_rel brfeat ON brsongs.songId = brfeat.songId
LEFT JOIN br_artists feat ON brfeat.artistId = feat.artistId
WHERE bralbums.albumId = ?
GROUP BY brsongs.songId, bralbums.cover, bralbums.albumName, bralbums.releaseDate
ORDER BY brsongs.songTitle;

    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query non valida: " . $conn->error); // Stampa l'errore se la query fallisce
    }

    $stmt->bind_param("s", $_GET['albumId']); // Associa l'albumId alla query

    $stmt->execute();
    $result = $stmt->get_result();

    // Controlla se ci sono risultati e restituisci gli album
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Recupera tutti i risultati come array associativo
    } else {
        return null; // Nessun album trovato
    }
}

$data_album = getAlbum($conn);
?>



<!-- HTML per visualizzare il banner -->
<div class="banner-container mb-5">
<?php if ($data_album): ?>
    <div class="album-row d-flex flex-column flex-md-row flex-sm-column">
        <div class="album-image col-lg-3 col-md-4 col-sm-4 col-xs-4 me-md-5 mb-3 mb-md-0 mb-sm-3" id="album-image-container">
            <img class="w-100 " id="album-img" src="<?php echo htmlspecialchars($data_album[0]['cover']); ?>" alt="<?php echo htmlspecialchars($data_album['albumName']); ?>" crossorigin="anonymous">
        </div>
        <div class="d-flex flex-column">
            <div class="album-name">
                <span><?php echo htmlspecialchars($data_album[0]['albumName']); ?></span>
            </div>
            <small class="mb-2 text-body-tertiary">
                by <i>
                <?php 
                    // Converti gli artistIds e artistNames in array usando "|" come separatore
                    $artistIds = explode('|', $data_album[0]['albumArtistIds']);
                    $artistNames = explode('|', $data_album[0]['albumArtistNames']);
                    
                    $dateString = $data_album[0]["releaseDate"];
                    if(strlen($dateString) > 5){
                        $date = DateTime::createFromFormat('Y-m-d', $dateString);
                        $formattedDate = $date->format('d/m/Y');
                    }else{
                        $formattedDate = $dateString;
                    }
                    
                    // Itera sugli artisti e crea un link per ciascuno
                    $artistLinks = [];
                    foreach ($artistIds as $index => $artistId) {
                        $artistName = htmlspecialchars($artistNames[$index]);
                        $artistLinks[] = "<a href='artist.php?artistId=" . urlencode($artistId) . "' class='text-decoration-none'>$artistName</a>";
                    }

                    
                    // Unisci i link con una virgola
                    echo implode(', ', $artistLinks);
                    echo ' · ' .$formattedDate;
                ?></i>
            </small>
        </div>
        
    </div>

    <div class="album-tracklist-container">
    <ol class="tracklist-track-name-ol">
    <?php foreach ($data_album as $song): ?>
        <li class="tracklist-track-name text-body-tertiary fw-bold">
            <div class="d-flex flex-row" style="justify-content:space-between">
                <span><?php echo $song['songTitle']; ?> </span>

                <!-- Sistema di valutazione a stelle univoco per ciascuna canzone -->
                <fieldset class="rate">
                <input type="radio" id="rating20_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="10" />
                    <label for="rating20_<?php echo $song['songId']; ?>" title="10 stars"></label>

                    <input type="radio" id="rating19_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="19" />
                    <label class="half" for="rating19_<?php echo $song['songId']; ?>" title="9.5 stars"></label>

                    <input type="radio" id="rating18_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="18" />
                    <label for="rating18_<?php echo $song['songId']; ?>" title="9 stars"></label>

                    <input type="radio" id="rating17_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="17" />
                    <label class="half" for="rating17_<?php echo $song['songId']; ?>" title="8.5 stars"></label>

                    <input type="radio" id="rating16_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="16" />
                    <label for="rating16_<?php echo $song['songId']; ?>" title="8 stars"></label>

                    <input type="radio" id="rating15_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="15" />
                    <label class="half" for="rating15_<?php echo $song['songId']; ?>" title="7.5 stars"></label>

                    <input type="radio" id="rating14_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="14" />
                    <label for="rating14_<?php echo $song['songId']; ?>" title="7 stars"></label>

                    <input type="radio" id="rating13_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="13" />
                    <label class="half" for="rating13_<?php echo $song['songId']; ?>" title="6.5 stars"></label>

                    <input type="radio" id="rating12_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="12" />
                    <label for="rating12_<?php echo $song['songId']; ?>" title="6 star"></label>

                    <input type="radio" id="rating11_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="11" />
                    <label class="half" for="rating1_<?php echo $song['songId']; ?>" title="5.5 star"></label>

                    <input type="radio" id="rating10_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="10" />
                    <label for="rating10_<?php echo $song['songId']; ?>" title="5 stars"></label>

                    <input type="radio" id="rating9_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="9" />
                    <label class="half" for="rating9_<?php echo $song['songId']; ?>" title="4.5 stars"></label>

                    <input type="radio" id="rating8_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="8" />
                    <label for="rating8_<?php echo $song['songId']; ?>" title="4 stars"></label>

                    <input type="radio" id="rating7_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="7" />
                    <label class="half" for="rating7_<?php echo $song['songId']; ?>" title="3.5 stars"></label>

                    <input type="radio" id="rating6_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="6" />
                    <label for="rating6_<?php echo $song['songId']; ?>" title="3 stars"></label>

                    <input type="radio" id="rating5_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="5" />
                    <label class="half" for="rating5_<?php echo $song['songId']; ?>" title="2.5 stars"></label>

                    <input type="radio" id="rating4_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="4" />
                    <label for="rating4_<?php echo $song['songId']; ?>" title="2 stars"></label>

                    <input type="radio" id="rating3_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="3" />
                    <label class="half" for="rating3_<?php echo $song['songId']; ?>" title="1.5 stars"></label>

                    <input type="radio" id="rating2_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="2" />
                    <label for="rating2_<?php echo $song['songId']; ?>" title="1 star"></label>

                    <input type="radio" id="rating1_<?php echo $song['songId']; ?>" name="rating_<?php echo $song['songId']; ?>" value="1" />
                    <label class="half" for="rating1_<?php echo $song['songId']; ?>" title="0.5 star"></label>
                </fieldset>
            </div>
        </li>    
    <?php endforeach; ?>
    </ol>
</div>

<?php else: ?>
    <p>Artista non trovato.</p>
<?php endif; ?>
</div>


<style>
.album-row {
    display: flex;
    align-items: center; /* Allinea verticalmente l'immagine e il testo */
    margin: 20px; /* Spaziatura intorno alla riga */
    
}

.album-tracklist-container{
    margin: 20px;
}

.tracklist-track-name{
    padding: 5px 5px 5px 0px;
    border-bottom: 1px solid #e9e9e9;
    font-size: 2vw;
}

.tracklist-track-name-ol li{
    font-size:2.5vh;

}

.album-image {
    position: relative; /* Necessario per il posizionamento assoluto dell'ombra */
    overflow: hidden; 
    border-radius: 3%; 
}

.album-image img {
    position: relative; /* Necessario per mantenere l'immagine sopra l'ombra */
    z-index: 1; /* Porta l'immagine sopra l'ombra */
}

.album-img-shadow {
    position: absolute; /* Posiziona l'ombra sopra l'immagine */
    top: 0; /* Allinea l'ombra all'inizio del contenitore */
    left: 0; /* Allinea l'ombra all'inizio del contenitore */
    width: 100%; /* Larghezza dell'ombra */
    height: 100%; /* Altezza dell'ombra */
    border-radius: 50%; /* Rende l'ombra a forma di cerchio */
    filter: blur(15px); /* Sfoca l'ombra per un effetto morbido */
    z-index: 0; /* Manda l'ombra dietro all'immagine */
    background-color: rgba(0, 0, 0, 0); /* Imposta un colore di sfondo trasparente di default */

}

.album-name span {
    font-size: 4.5vw;
    margin: 0; /* Rimuove il margine predefinito */
}

/* Ratings widget */
.rate {
    display: inline-block;
    border: 0;
}
/* Hide radio */
.rate > input {
    display: none;
}
/* Order correctly by floating highest to the right */
.rate > label {
    float: right;
}
/* The star of the show */
.rate > label:before {
    display: inline-block;
    font-size: 1.1rem;
    padding: .3rem .2rem;
    margin: 0;
    cursor: pointer;
    font-family: FontAwesome;
    content: "\f005 "; /* full star */
}
/* Zero stars rating */
.rate > label:last-child:before {
    content: "\f006 "; /* empty star outline */
}
/* Half star trick */
.rate .half:before {
    content: "\f089 "; /* half star no outline */
    position: absolute;
    padding-right: 0;
}
/* Click + hover color */
input:checked ~ label, /* color current and previous stars on checked */
label:hover, label:hover ~ label { color: #f57412;  } /* color previous stars on hover */

/* Hover highlights */
input:checked + label:hover, input:checked ~ label:hover, /* highlight current and previous stars */
input:checked ~ label:hover ~ label, /* highlight previous selected stars for new rating */
label:hover ~ input:checked ~ label /* highlight previous selected stars */ { color: #f57412;  } 


</style>

<script>

$(document).ready(function() {
    const img = $('#album-img')[0]; // Ottieni l'elemento immagine
    const shadow = $('.album-image'); // Ottieni l'elemento ombra

    $(img).on('load', function() {
        const colorThief = new ColorThief();
        const dominantColor = colorThief.getColor(img); // Ottieni il colore dominante
        const rgbColor = `rgb(${dominantColor[0]}, ${dominantColor[1]}, ${dominantColor[2]})`;

        // Imposta il colore dell'ombra
        shadow.css('box-shadow', '0em 0em 1em 1px ' + rgbColor);
    });

    // Assicurati che l'ombra si imposti anche se l'immagine è già stata caricata
    if (img.complete) {
        $(img).trigger('load');
    }
});
</script>
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
    <div class="album-row">
        <div class="album-image" id="album-image-container">
            <img id="album-img" src="<?php echo htmlspecialchars($data_album[0]['cover']); ?>" alt="<?php echo htmlspecialchars($data_album['albumName']); ?>" crossorigin="anonymous">
        </div>
        <div class="d-flex flex-column">
            <div class="album-name">
                <h1><?php echo htmlspecialchars($data_album[0]['albumName']); ?></h1>
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
        <ol>
        <?php foreach ($data_album as $song): ?>
                <li class="tracklist-track-name text-body-tertiary fw-bold"><?php echo $song['songTitle'];?></li>    
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
    font-size:1.4rem
}

.album-image {
    position: relative; /* Necessario per il posizionamento assoluto dell'ombra */
    width: 20em; 
    height: 20em; 
    overflow: hidden; 
    border-radius: 3%; 
    margin-right: 4em; 
}

.album-image img {
    width: 100%; /* L'immagine riempie il contenitore */
    height: auto; /* Mantiene le proporzioni */
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

.album-name h1 {
    font-size: 5em;
    margin: 0; /* Rimuove il margine predefinito */
}

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
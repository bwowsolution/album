<?php
session_start();
require_once 'db.php';

function getArtist($conn) {
    // Verifica se il parametro 'artistId' è settato e non vuoto
    if (!isset($_GET['artistId']) || empty($_GET['artistId'])) {
        return null;
    }

    // Prepara la query in modo sicuro per evitare SQL Injection
    $sql = "SELECT artistName, img FROM br_artists WHERE artistId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_GET['artistId']); // Associa il parametro 'artistId' alla query
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

$artist = getArtist($conn);
?>



<!-- HTML per visualizzare il banner -->
<div class="banner-container mb-5">
<?php if ($artist): ?>
    <div class="artist-row d-flex flex-column flex-md-row flex-sm-column">
        <div class="artist-image col-lg-3 col-md-4 col-sm-4 col-xs-4 me-md-5 mb-3 mb-md-0 mb-sm-3 p-0" id="artist-image-container">
            <img id="artist-img" src="<?php echo htmlspecialchars($artist['img']); ?>" alt="<?php echo htmlspecialchars($artist['artistName']); ?>" crossorigin="anonymous">
        </div>
        <div class="artist-name col-md-6">
            <span><?php echo htmlspecialchars($artist['artistName']); ?></span>
        </div>
    </div>
<?php else: ?>
    <p>Artista non trovato.</p>
<?php endif; ?>
</div>


<style>
.artist-row {
    display: flex;
    align-items: center; /* Allinea verticalmente l'immagine e il testo */
    margin: 20px; /* Spaziatura intorno alla riga */
}

.artist-image {
    position: relative; /* Necessario per il posizionamento assoluto dell'ombra */
    width: 30vh;
    height: 30vh;
    overflow: hidden; 
    border-radius: 50%; 
}

.artist-image img {
    width: 30vh;
    height: auto; /* Mantiene le proporzioni */

    position: relative; /* Necessario per mantenere l'immagine sopra l'ombra */
    z-index: 1; /* Porta l'immagine sopra l'ombra */
}

.artist-img-shadow {
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

.artist-name span {
    font-size: 4.5vh;
    margin: 0; /* Rimuove il margine predefinito */
}

/* Apply mobile adjustments */
@media (max-width: 768px) { /* Adjust breakpoint as needed for mobile */
    .artist-image {
        position: relative; /* Necessario per il posizionamento assoluto dell'ombra */
        width: 30vh; 
        height: 30vh; 
        overflow: hidden; 
        border-radius: 50%; 
        margin-bottom: 2em; 
    }

    .artist-image img {
        width: 30vh;
        height: auto; /* Mantiene le proporzioni */
        position: relative; /* Necessario per mantenere l'immagine sopra l'ombra */
        z-index: 1; /* Porta l'immagine sopra l'ombra */
    }

    .artist-name span {
        font-size: 4.5vh;
        margin: 0; /* Rimuove il margine predefinito */
    }

    .artist-row{
        display:flex;
        flex-direction:column;
        margin:0 auto;
        text-align:center;
    }
}

</style>

<script>

$(document).ready(function() {
    const img = $('#artist-img')[0]; // Ottieni l'elemento immagine
    const shadow = $('.artist-image'); // Ottieni l'elemento ombra

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
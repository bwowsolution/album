<?php
	$title = 'Dashboard';
	require('header.php'); 
?>


    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    	<div class="box p-4 w-100">
    		qui dashboard di statistiche all time. tipo:
    		<ul class="mb-0 mt-2">
    			<li>top 5 album all time voto medio utenti</li>
    			<li>top 5 artisti voto medio utenti</li>
    			<li>ecc</li>
    		</ul>
    	</div>
    </div>
    
    
    
    
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <?php echo $_SESSION['user']; ?>
      </div>







      <p>Prova</p>
    </main>
 
<?php require('footer.php'); ?>
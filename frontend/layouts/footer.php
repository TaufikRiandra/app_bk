	<?php
	// Tutup content-area dan main-layout jika sidebar diinclude
	if (isset($GLOBALS['sidebar_included']) && $GLOBALS['sidebar_included']) {
		echo '</section></div>';
	}
	?>
	</main>
	<footer class="site-footer">
		<div class="container">
			<p>© Sistem BK • Bimbingan Konseling Sekolah</p>
		</div>
	</footer>
</body>
</html>

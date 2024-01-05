<div class="lf-action-wrap">
<div class="lf-ui-sidebar-wrapper">

	<div class="lf-inside">

		<div class="lf-panel">

			<div class="lf-panel-header">
				<h3><?php _e( 'LeafBridge Plugin Minimum Requirements', 'leafbridge' ); ?></h3>
			</div>

			<div class="lf-panel-content">

				<div>
					<table>
						<tr>
							<td><strong>WordPress</strong></td>
							<td>
							<?php
							$wpversion = get_bloginfo( 'version' );							
							if($wpversion >= 5.5) {
								echo '<span class="lf-server-status lf-server-success"></span>';
								echo '<strong>'.$wpversion.'</strong> - Congratulations! This meets or exceeds our mimimum WordPress version 5.5 or greater.';
							} else {
								echo '<span class="lf-server-status lf-server-fail"></span>';
								echo '<strong>'.$wpversion.'</strong> - We recommend mimimum WordPress version 5.5 or greater for the best experience.';
							}
							?>
							</td>
						</tr>
						
						<tr>
							<td><strong>Database</strong></td>
							<td>
							<?php
							$mysql = LeafBridge_Compatibility::get_mysql();							
							if($mysql >= 5.7) {
								echo '<span class="lf-server-status lf-server-success"></span>';
								echo '<strong>'.$mysql.'</strong> - Congratulations! This meets or exceeds our recommendation of 5.7 or greater OR MariaDB version 10.3 or greater.';
							} else {
								echo '<span class="lf-server-status lf-server-fail"></span>';
								echo '<strong>'.$mysql.'</strong> - We recommend 5.7 or greater OR MariaDB version 10.3 or greater for the best experience.';
							}
							?>
							</td>
						</tr>
						
						<tr>
							<td><strong>Web Server</strong></td>
							<td>
							<?php 
							if($_SERVER['SERVER_SOFTWARE'] == 'Apache' || $_SERVER['SERVER_SOFTWARE'] == 'Nginx') {
								echo '<span class="lf-server-status lf-server-success"></span>';
								echo '<strong>'.$_SERVER['SERVER_SOFTWARE'].'</strong> - Congratulations! This meets or exceeds our recommendation of Nginx or Apache with mod_rewrite module.';
							} else {
								echo '<span class="lf-server-status lf-server-fail"></span>';
								echo '<strong>'.$_SERVER['SERVER_SOFTWARE'].'</strong> - We recommend Nginx or Apache with mod_rewrite module for the best experience.';
							}
							?>
							</td>
						</tr>
						
						<tr>
							<td><strong>PHP: Version</strong></td>
							<td>
							<?php 
									if(PHP_VERSION >= 7.3) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.PHP_VERSION.'</strong> - Congratulations! This meets or exceeds our recommendation of version 7.3 or greater.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.PHP_VERSION.'</strong> - We recommend version 7.3 or greater for the best experience.';
									}
									?>
							</td>
						</tr>
						
						
						<tr>
							<td><strong>PHP: memory_limit</strong></td>
							<td>
							<?php 
									$memory1 = ini_get( 'memory_limit' );
									$memory = LeafBridge_Compatibility::return_bytes($memory1);
									if($memory >= (512 * 1024 * 1024)) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.$memory1.'</strong> - Congratulations! This meets our recommendation of 512M RAM.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.$memory1.'</strong> - We recommend 512M greater RAM for the best experience.';
									}
									?>
							</td>
						</tr>
						
						<tr>
							<td><strong>PHP: post_max_size</strong></td>
							<td>
							<?php 
									$post_max_size1 = trim(ini_get( 'post_max_size' ));
									$post_max_size = LeafBridge_Compatibility::return_bytes($post_max_size1);
									if($post_max_size >= (64 * 1024 * 1024)) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.$post_max_size1.'</strong> - Congratulations! This meets our recommendation of 64M post_max_size.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.$post_max_size1.'</strong> - We recommend 64M greater for the best experience.';
									}
							 ?>
							</td>
						</tr>
						
						<tr>
							<td><strong>PHP: max_execution_time</strong></td>
							<td>
							<?php 
									$max_execution_time = ini_get( 'max_execution_time' );
									if($max_execution_time >= 3600) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.$max_execution_time.'</strong> - Congratulations! This meets our recommendation of 3600 seconds max_execution_time.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.$max_execution_time.'</strong> - We recommend 3600 seconds greater for the best experience.';
									}
							 ?>
							</td>
						</tr>
						 
						<tr>
							<td><strong>PHP: upload_max_file size</strong></td>
							<td>
							<?php 
									$upload_max_filesize1 = ini_get( 'upload_max_filesize' );
									$upload_max_filesize = LeafBridge_Compatibility::return_bytes($upload_max_filesize1);
									if($upload_max_filesize >= (30 * 1024 * 1024)) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.$upload_max_filesize1.'</strong>  - Congratulations! This meets our recommendation of 30M upload_max_file size.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.$upload_max_filesize1.'</strong>  - We recommend 30M greater for the best experience.';
									}
							 ?>
							</td>
						</tr>
						
						
						<tr>
							<td><strong>PHP: max_input_vars</strong></td>
							<td> 
									<?php 
									$max_input_vars = ini_get( 'max_input_vars' );
									if($max_input_vars >= 1000) {
										echo '<span class="lf-server-status lf-server-success"></span>';
										echo '<strong>'.$max_input_vars.'</strong>  - Congratulations! This meets our recommendation of 1000 max_input_vars.';
									} else {
										echo '<span class="lf-server-status lf-server-fail"></span>';
										echo '<strong>'.$max_input_vars.'</strong>  - We recommend 1000 greater for the best experience.';
									}
									?>
							</td>
						</tr>
						
						<tr>
							<td><strong>Hardware Requirements</strong></td>
							<td>
								<ul>
									<li>Disk Space: 1GB+</li>
									<li>RAM: 512M+</li>
									<li>Processor: 1.0GHz+</li>
								</ul>
							</td>
						</tr>
						<tr>
							<td><strong>Software</strong></td>
							<td>Google Chrome, Firefox or Edge Web Browser. <br/>
							Website should have HTTPS and all the HTTP URLs should be redirected to HTTPS URLs.</td>
						</tr>
						<tr>
							<td><strong>Other Requirement</strong></td>
							<td>Dutchie Plus API Key and API Secret Key. License key is required to use the LeafBridge Pro version.</td>
						</tr>
					</table>
					 
				</div>
				 
		   </div>
		</div>
		
		
		
		<div class="lf-panel">

			<div class="lf-panel-header">
				<h3><?php _e( 'Help & Troubleshooting', 'leafbridge' ); ?></h3>
			</div>

			<div class="lf-panel-content">

				<div>
					<p>
						<?php
						printf(
							__( 'Free support is available on the <a href="%s">Dutchie Support Team</a>.', 'leafbridge' ),
							'https://dutchie.com/contact/'
						)
						?>
					</p>
					<p>
						<?php
						printf(
							__( '<a href="%s" style="font-weight:bold;" target="_blank">Upgrade</a> to gain access to premium features and priority email support.', 'leafbridge' ),
							'https://dutchie.com/contact'
						);
						?>
					</p>
					<p>
						<?php
						printf(
							__( 'Found a bug or have a feature request? Please submit an issue on <a href="%s">Dutchie</a>!', 'leafbridge' ),
							'https://dutchie.com/contact/'
						);
						?>
					</p>
				</div>
				
				<!--System Info-->
				<div class="lf-row">
					<div class="lf-input-text lf-full-width">
						<label><strong><?php _e( 'System Info', 'leafbridge' ); ?></strong></label>
						<textarea readonly="readonly" name='lf-sysinfo' class="lf-textarea"><?php echo LeafBridge_Compatibility::get_sysinfo(); ?></textarea>
					</div>
				</div>

				 

		   </div>
		</div>
		
		
	</div>
 
</div>
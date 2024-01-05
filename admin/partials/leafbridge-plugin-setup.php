<div class="lf-action-wrap">
	<div class="lf-ui-sidebar-wrapper">

		<div class="lf-inside">

			<div class="lf-panel">

				<div class="lf-panel-header">
					<h3><?php _e('LeafBridge Plugin Setup Documentation', 'leafbridge'); ?></h3>
				</div>

				<div class="lf-panel-content">

					<div>
						<h3>Instructions</h3>

						<p>1. Download the leafbridge.zip file</p>

						<p>2. Go to your WordPress left menu, click "Add New" under the "Plugins" menu item.</p>

						<p>3. Click the "Upload Plugin" button.</p>

						<p>4. Upload the "leafbridge.zip" file and Install it.</p>

						<p>5. Once activated the plugin click the "Setting" link.</p>

						<p>6. Enter your Dutchie API key and click save button to connect and activate the plugin.</p>

						<p>7. Once connected to the dutchie API key you will see the activation status and trailer stores.
							Select preferred retailer stores and click the "Setup My Store" button to complete the plugin installation.</p>




					</div>

					<div>

						<h3>Troubleshooting</h3>

						<p>1. Make sure your API key and API Secret Key are correct and activated from plugins settings.</p>

						<p>2. If the product page URLs are not working go to WP Dashboard > Settings > Permalinks > Select "Post Name" option and save changes.<br />
							(Even if the same option is selected just save changes again) Then single product page links should work.</p>

						<p>3. Clear your browser cache and check.</p>

						<p>4. If you’re using any cache plugin, then purge cache.</p>

						<p>5. Try on a different web browser.</p>
						<?php
						$timezone = new DateTimeZone('America/New_York');

						$timestamp =  wp_next_scheduled('leafbridge_sync_hook');
						$date = new DateTime("@$timestamp");
						$date->setTimezone($timezone);
						$formatted_date_time = $date->format('dS M Y g.ia');


						$timeNow = time();
						$date1 = new DateTime('@' . $timestamp);
						$date1->setTimezone($timezone);
						$date2 = new DateTime('@' . $timeNow);
						$date2->setTimezone($timezone);

						$timeDifference = $date2->diff($date1);
						$hoursDifference = $timeDifference->h;
						$minutesDifference = $timeDifference->i;

						echo "<p class='lb-cron-notice'>Automatic synchronization is scheduled at : $formatted_date_time.</p>";
						echo "<p class='lb-cron-notice'>Next run in : $hoursDifference hours $minutesDifference minutes.</p>";
						?>


					</div>

				</div>




			</div>




		</div>

	</div>
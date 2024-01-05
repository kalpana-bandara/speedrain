<div class="wrap">
	<div class="metabox-holder">
<?php
$retailers = new LeafBridge_Retailers();
		
	 $results_retailers = $retailers->get_retailers_details('basic');
 //var_dump($results_retailers); 
	 if(isset($results_retailers)) {
		 $dutchie_store_status = 1;
		 $results_retailers->reformatResults(true);
		 $dutchie_store_retailers = $results_retailers->getData()['retailers'];  
	  
	 }
 
?>		
<?php
$r = new LeafBridge_Products();
	
$retailerId = 'f0ff5c46-2f0c-4137-941b-b79b71e1d85c';
$retailerIdname = '';
		if(isset($_GET['r'])) {
			$retailerId = $_GET['r'];
			$retailerIdname = $_GET['name'];
		}
$pagination = "{ limit: 15 offset: 0 }";
$filter     = "{ }";
$sort       = "{ direction: ASC key: NAME }";
	
 $tt = $r->fetch_retailer_products_no_menu($retailerId, $pagination, $filter, $sort); 

?>
<h2>Products <?php echo ($retailerIdname !='' ? 'of '.$retailerIdname : ''); ?></h2>

<div class="lb-admin-select-retailer-product">
<ul>
	
 <?php $i=0; foreach($dutchie_store_retailers as $u) {
	if($i==0 && $retailerIdname =='') {
		echo '<li class="lb-admin-active-store"><a href="/wp-admin/admin.php?page=products&r='.$u['id'].'&name='.$u['name'].'">'.$u['name'].'</a></li>';
	} else {
		echo '<li><a href="/wp-admin/admin.php?page=products&r='.$u['id'].'&name='.$u['name'].'">'.$u['name'].'</a></li>';
	}
	
$i++; } 
		?>
	
	</ul>
		</div>		
<table class="widefat striped fixed">
    <thead>
        <tr>
            <th>Name</th>
			<th>Product image</th>
            <th>Category</th>
            <th>Strain Type</th>
			<th>Quantity</th>
        </tr>
    </thead>

    <tbody>
        
		<?php
		foreach($tt as $y) { ?>
			<tr>
				<td><?php echo $y['name'];?></td>
				<td><img src="<?php echo $y['image'];?>" width="100" /></td>
				<td><?php echo $y['category'];?></td>
				<td><?php echo $y['strainType'];?></td>
				<td><?php echo $y['variants'][0]['quantity'];?></td>
        	</tr>
		<?php } ?>
		
    </tbody>

    <tfoot>
        <tr>
            <th>Name</th>
			<th>Product image</th>
            <th>Category</th>
            <th>Strain Type</th>
			<th>Quantity</th>
        </tr>
    </tfoot>  
</table>

<?php
	  echo '<pre>';print_r(($tt));	echo '</pre>';	 

  ?>
	
</div>
	</div>

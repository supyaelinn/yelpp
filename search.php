<?php 
	ini_set('display_errors', '1');
	require("api.php");

	if(!empty($_POST))
	{

		$yp = new Yelp();
		$list = $yp->getAllList();

		// echo count($list);exit;
		// $list = json_encode($list);

		// echo $list[0];exit;
		$i = 1;
		echo '<h1> 1000 Results per search in ' . $_POST['city'] .' </h1>';
		echo '<h3> Limited 100 Results only for demo </h3>';
		// <div class='full-block' style='height:4300px;'>
		echo "<form name='data' action='export.php' method='POST' target='hidden-form'>";
		echo "<div class='content-col'>
		<div class='list-items'>";
		
		// echo '<input type="submit" name="save" value="Export Excel">';
		foreach ($list as  $res) {
		if($res->title != ""){
?>		
	
		<div class="item" >
			<div class="item-pic">
				<img src="img/logo1.png">
			</div><!-- .item-pic -->

			<div class="item-description">
				<div class="item-title-row">
					<div class="item-counter"><div class="item-counter-inner"><?= $i ?></div></div>
					<input type="hidden" name="item[<?= $i ?>][title]" value="<?= $res->title ?>">
					<input type="hidden" name="item[<?= $i ?>][url]" value="<?= $res->url ?>">
					<h2><a href="<?= $res->url ?>" title="<?= $res->title ?>"><?= $res->title ?></a></h2>
				</div>
				<div class="item-ratings-wrapper">
					<div class="item-rating" data-rating="5.000000" title="gorgeous">
						
				</div>
					<div class="item-ratings-count">
						 									</div>
					<div class="clear"></div>
				</div><!-- .item-ratings-wrapper -->
				<div class="item-info">
					<div class="item-addr">
					<?php if($res->address != "null"){ ?>
						<input type="hidden" name="item[<?= $i ?>][address]" value="<?= $res->address ?>">
						<strong><?= $res->address ?></strong>
					<?php } ?>
					</div>

					<div class="item-phone">
						<input type="hidden" name="item[<?= $i ?>][phone]" value="<?= $res->phone ?>">
						<i class="fa fa-phone-square"></i><?= $res->phone ?>
					</div>
					<div class="item-url">
						<input type="hidden" name="item[<?= $i ?>][category]" value="<?= $res->category ?>">
						<i class="fa fa-website"></i><?= $res->category ?>
					</div>
				</div>
				</div>

			<div class="clear"></div>
		</div>
		
<?php 
$i++;
}
}
	?>
	</div>
	</div>
	<!-- </div> -->
<?php
}
 ?>
 	<div class="sidebar">
 		<input type="submit" name="save"  value="Export Excel">
 	</div>
 </form>
 	<script type="text/javascript">
 		$("#save").click(function(){
 			data = $("form[name=data]").serialize();
 			// $("form[name=data]").ajaxSubmit({url: 'export.php', type: 'post',data: data});
 			$.post('export.php',data);
 			// $.ajax( {
		  //     type: "POST",
		  //     url: 'export.php',
		  //     data: data,
		  //     success: function( response ) {
		  //     	window.open(this.url,'_blank' );
		  //     //    var $a = $("<a>");
				//     // $a.attr("href",data.file);
				//     // $("body").append($a);
				//     // $a.attr("download","file.xls");
				//     // $a[0].click();
				//     // $a.remove();
		  //     }
		  //   } );
 			// $.ajax({
	   //          type: 'POST',
	   //          data: data,
	   //          url: 'export.php',
	   //          dataType: 'json',
	   //          // async: false,
	   //          success: function(result){
	   //              // call the function that handles the response/results
	   //               console.log(result);
	   //              //$(".wrapper").html(result);
	   //              //$('.loading').modal('toggle');
	                
	   //          },
	   //          error: function(){
	                
	   //              window.alert("Wrong query : ");
	   //              //$('.loading').modal('toggle');
	   //              //$("#btn_search").click();
	   //          }
	   //        });
 			// $(".item").each(function(){
 			// 	console.log($(this).val());
 			// });
 		});
 	</script>

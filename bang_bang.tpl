{OVERALL_GAME_HEADER}

<div id="board" style="width:100%;">
	<div style="position:absolute; top:30px; right:30px;">
		<p id="deck" class="center" style="height:20px; text-align:center;"></p>
	</div>
	<div style="position:absolute; top:0px; left: 0px; width:100%; height:100%; display:flex; flex-direction:column;">
		<div id="hand" class="playarea left right" style=" height:350px; background-color:blue; top:0px; margin-left:auto; margin-right:auto;">
			<h3 style="font-size:28px; text-align:center">Your Hand</h3>
			<div id="yourHand" class="mrow" style="width:100%">			
			</div>
			<p><input type="checkbox" id="checkDesc"/> Show descriptions</p>
		</div>
		<div id="options" class="playarea" style="display:none; flex-grow:1; background-color:white; margin-left:auto; margin-right:auto; justify-content:space-evenly"> 
			<h3 id="optionsTitle" style="text-align:center">Choose</h3>		
		</div>
		<div id="gameareas" class="mrow" style="width:100%; justify-content:space-between; flex-grow:0">
			<div id="gameareasLeft" style="width:45%; height:100%; display:flex; flex-direction:column; justify-content:space-evenly">
				<!-- BEGIN playarealeft -->
					<div id="playarea_{X}" class="playarea">
						<h1 id="title_{X}"></h1>
						<div class="mrow" style="width:95%">
							<div id="hand_{X}" class="card" style="display:flex; flex-direction:mrow; align-items: center; justify-content: space-evenly; ">
								<p id="handCount_{X}" style="text-align:center;"/>
							</div>
							<div id="cards_{X}" class="mrow" style="flex-grow:1">
								
							</div>
						</div>
					</div>
				<!-- END playarealeft -->   
			</div>
			<div id="gameareasRight" style="width:45%;height:100%; display:flex; flex-direction:column; justify-content:space-evenly">
				<!-- BEGIN playarearight -->
					<div id="playarea_{X}" class="playarea">
						<h1 id="title_{X}"></h1>
						<div class="mrow" style="width:95%">
							<div id="hand_{X}" class="card" style="display:flex; flex-direction:mrow; align-items: center; justify-content: space-evenly; ">
								<p id="handCount_{X}" style="text-align:center;"/>
							</div>
							<div id="cards_{X}" class="mrow" style="flex-grow:1">
								
							</div>
						</div>
					</div>
				<!-- END playarearight -->   
			</div>
		</div>
    </div>
</div>


<script type="text/javascript">

var jstpl_card = '<div class="card bigcard" id="tmpcard" style="position:absolute; top:${y}px; left:${x}px; scale:1; background-position: ${pos}"></div>';
var jstpl_option = '<p style="text-align: center; color: #${color}; font-weight: bold; cursor: pointer;" id="option_${id}">${name}</p>';

</script>  

{OVERALL_GAME_FOOTER}

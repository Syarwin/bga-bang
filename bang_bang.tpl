{OVERALL_GAME_HEADER}

<div id="board"></div>

<script type="text/javascript">
var jstpl_hand = `<div id="hand">
</div>
`;

var jstpl_player = `<div class='bang-player' id='bang-player-\${id}' data-role="\${role}" data-no="\${no}">
	<div class='bang-player-container'>
		<div class='player-name' style="color:#\${color}">\${name}</div>
		<div class='player-cards'>\${handCount}</div>
	</div>
</div>`;


var jstpl_card = `<div class="bang-card" id="bang-card-\${id}" data-type="\${type}">
		<div class="card-name">\${name}</div>
		<div class="card-background"></div>
		<div class="card-symbols"></div>
</div>`;

//var jstpl_card = '<div class="card bigcard" id="tmpcard" ></div>';

//var jstpl_card = '<div class="card bigcard" id="tmpcard" style="position:absolute; top:${y}px; left:${x}px; scale:1; background-position: ${pos}"></div>';
var jstpl_option = '<p style="text-align: center; color: #${color}; font-weight: bold; cursor: pointer;" id="option_${id}">${name}</p>';

</script>

{OVERALL_GAME_FOOTER}

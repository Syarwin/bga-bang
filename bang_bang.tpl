{OVERALL_GAME_HEADER}

<div id="board"></div>

<script type="text/javascript">
var jstpl_hand = `<div id="hand">
	<div id="role-container" data-role="\${role}">
		<div id="role-card">
			<div id="role-name">\${role-name}</div>
			<div id="role-text">\${role-text}</div>
		</div>
	</div>
	<div id="hand-cards"></div>
</div>
`;

var jstpl_table = `<div id="table">
	<div id="table-container">
		<div id="deck">\${deck}</div>
		<div id="discard"></div>
	</div>
</div>
`;

var jstpl_player_board_role = `<div class='player-role' id="player-role-\${id}" data-role='\${role}'></div>`;

var jstpl_player = `<div class='bang-player' id='bang-player-\${id}' data-role="\${role}" data-no="\${no}" data-max-bullets="\${bullets}" data-bullets="\${hp}" data-hand="\${handCount}">
	<div class='bang-player-container'>
		<div class='player-inplay' id="player-inplay-\${id}">
		</div>
		<div id="player-character-\${id}" class='player-info' data-character='\${characterId}'>
			<div class="player-character-name">\${character}</div>
			<div class="player-character-background"></div>
			<div class='player-name' style="color:#\${color}">\${name}</div>
			<div class='player-cards'>
				<span class="player-hand-card"></span>
				<span class='player-handcount'></span>/<span class="player-maxhand"></span>
			</div>

			<ul class='player-bullets' id='player-bullets-\${id}' >
				<li class='bullet'></li>
				<li class='bullet'></li>
				<li class='bullet'></li>
				<li class='bullet'></li>
				<li class='bullet'></li>
				<li class='bullet'></li>
			</ul>
			<div class="player-star"></div>
		</div>
	</div>
</div>`;

var jstpl_characterTooltip = `<div class="bang-character-tooltip">
	<div class="bang-character-tooltip-sizing">
		<div class='player-info' data-character='\${characterId}'>
			<div class="player-character-name">\${character}</div>
			<div class="player-character-background"></div>
			<div class='player-character-powers'>\${powers}</div>
		</div>
	</div>
</div>`;


var jstpl_card = `<div class="bang-card \${flipped}" id="bang-card-\${id}" data-id="\${id}" data-type="\${type}">
	<div class="card-back"></div>
	<div class="card-front">
		<div class="card-name">\${name}</div>
		<div class="card-background"></div>
		<div class="card-copy">
			<span class="card-copy-value">\${value}</span>
			<span class="card-copy-color" data-color="\${color}"></span>
		</div>
	</div>
</div>`;

var jstpl_cardTooltip = `<div class="bang-card-tooltip">
	<div class="bang-card-tooltip-sizing">
		<div class="bang-card" id="bang-card-tooltip-\${id}" data-type="\${type}">
			<div class="card-back"></div>
			<div class="card-front">
				<div class="card-name">\${name}</div>
				<div class="card-background"></div>
				<div class="card-copy">
					<span class="card-copy-value">\${value}</span>
					<span class="card-copy-color" data-color="\${color}"></span>
				</div>
			</div>
		</div>
	</div>
	<p>
	\${text}
	</p>
</div>`;


var jstpl_dialog = `<div id="dialog-card-container"></div>
<div id="dialog-title-container"></div>
<div id="dialog-button-container"></div>
`;


//var jstpl_card = '<div class="card bigcard" id="tmpcard" ></div>';
//var jstpl_card = '<div class="card bigcard" id="tmpcard" style="position:absolute; top:${y}px; left:${x}px; scale:1; background-position: ${pos}"></div>';
var jstpl_option = '<p style="text-align: center; color: #${color}; font-weight: bold; cursor: pointer;" id="option_${id}">${name}</p>';
</script>

{OVERALL_GAME_FOOTER}

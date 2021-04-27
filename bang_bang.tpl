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

var jstpl_player_board_data = `<div class='bang-player-board' id="bang-player-board-\${id}" data-max-bullets="\${bullets}" data-bullets="\${hp}" data-hand="\${handCount}">
  <ul class='player-bullets'>
    <li class='bullet'></li>
    <li class='bullet'></li>
    <li class='bullet'></li>
    <li class='bullet'></li>
    <li class='bullet'></li>
    <li class='bullet'></li>
  </ul>
  <div class='player-cards'>
    <span class="player-hand-card"></span>
    <span class='player-handcount'></span>
  </div>
</div>`;

var jstpl_helpIcon = `
<div id='help-icon'>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
  <g class="fa-group">
    <path class="fa-secondary" fill="currentColor" d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48zM224 430a46 46 0 1 1 46-46 46.06 46.06 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.81-25.27-33-45.7-33-27.19 0-39.44 13.13-57.3 35.79a12 12 0 0 1-16.67 2.13L116.82 170a12 12 0 0 1-2.71-16.26C141.4 113 176.16 90 230.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path>
    <path class="fa-primary" fill="currentColor" d="M224 338a46 46 0 1 0 46 46 46.05 46.05 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.66 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.67-32.54 34C215.13 238.53 184 254.94 184 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path>
  </g>
</svg>
</div>
`;
var jstpl_helpDialog = `
<div id="dialog-roles"></div>
<div id="dialog-players"></div>
`;
var jstpl_helpDialogRole = `
<div id="dialog-role-\${role}" data-role="\${role}" class="dialog-role">
  <div class="dialog-role-count">
    <span id="dialog-role-count-\${role}">1</span>
    \${role-name}
  </div>
  <div class="dialog-role-img"></div>
  <div class="dialog-role-desc">\${role-text}</div>
</div>
`;

var jstpl_helpDialogCharacter = `
<div data-role="\${role}" class="dialog-character" data-character='\${characterId}'>
  <div class="dialog-player-name" style="color:#\${color}">\${name}</div>
  <div class="dialog-character-name">\${character}</div>
  <div class="dialog-character-background-container">
    <div class="dialog-character-background"></div>
  </div>
  <div class="dialog-character-desc">\${powers}</div>
</div>
`;


var jstpl_player_board_role = `<div class='player-role' id="player-role-\${id}" data-role='\${role}'></div>`;

var jstpl_player = `<div class='bang-player' id='bang-player-\${id}' data-role="\${role}" data-no="\${newNo}" data-max-bullets="\${bullets}" data-bullets="\${hp}" data-hand="\${handCount}">
	<div class='bang-player-container'>
		<div class='player-inplay' id="player-inplay-\${id}">
		</div>
		<div id="player-character-\${id}" class='player-info' data-character='\${characterId}'>
			<div class="player-character-name">\${character}</div>
			<div class="player-character-background">
        <div class="player-character-hourglass"></div>
      </div>
			<div class='player-name' style="color:#\${color};"><span style="background:\${background}">\${shortName}</span></div>
			<div class='player-cards'>
				<span class="player-hand-card"></span>
				<span class='player-handcount'></span>
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


var jstpl_card = `<div class="bang-card \${flipped}" id="bang-card-\${uid}" data-id="\${id}" data-type="\${type}">
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

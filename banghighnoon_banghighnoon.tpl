{OVERALL_GAME_HEADER}

<div id="board-wrapper">
  <div id="board"></div>
</div>

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
	    <div class="cards-row">
            <div id="deck">
              <span class="deckCount" id="mainDeckCount">\${deckCount}</span>
            </div>
            <div id="discard"></div>
	    </div>
	</div>
</div>
`;

var jstpl_events_row = `<div class="cards-row">
    <div id="eventNext">
        <span class="deckCount" id="eventsDeckCount"></span>
    </div>
    <div id="eventActive"></div>
</div>`

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

var jstpl_player = `<div class='bang-player' id='bang-player-\${id}' data-role="\${role}" data-no="\${newNo}">
	<div class='bang-player-container'>
		<div class='player-inplay' id="player-inplay-\${id}">
		</div>
		<div id="player-character-\${id}" class='player-info' data-character='\${characterId}' data-max-bullets="\${bullets}" data-bullets="\${hp}" data-hand="\${handCount}">
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
      <div class='player-distance' id='player-distance-\${id}'>0</div>
		</div>
	</div>
</div>`;

var jstpl_player_no_character = `<div class='bang-player' id='bang-player-\${id}' data-role="\${role}" data-no="\${newNo}"">
	<div class='bang-player-container'>
		<div class='player-inplay' id="player-inplay-\${id}">
		</div>
		<div id="player-character-\${id}" class='player-info empty'>
			<div class='player-name' style="color:#\${color};"><span style="background:\${background}">\${shortName}</span></div>
			<div class="player-star"></div>
      <div class='player-distance' id='player-distance-\${id}'>0</div>
		</div>
	</div>
</div>`;

var jstpl_character = `
	<div id='character-\${characterId}' class='player-info' data-character='\${characterId}' data-max-bullets='\${bullets}' data-bullets='\${bullets}'>
        <div class="player-character-name">\${character}</div>
        <div class="player-character-background"></div>
        <div class='player-character-powers'>\${powers}</div>
        <ul class='player-bullets'>
            <li class='bullet'></li>
            <li class='bullet'></li>
            <li class='bullet'></li>
            <li class='bullet'></li>
            <li class='bullet'></li>
            <li class='bullet'></li>
        </ul>
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


var jstpl_card = `<div class="bang-card \${flipped} \${extraClass}" id="bang-card-\${uid}" data-id="\${id}" data-type="\${type}">
	<div class="card-back"></div>
	<div class="card-front">
		<div class="card-name">\${name}</div>
		<div class="card-background"></div>
		<div class="card-copy">
			<span class="card-copy-value">\${value}</span>
			<span class="card-copy-color" data-color="\${color}" data-color-override="\${colorOverride}"></span>
			<span class="card-copy-color-override" data-color="\${color}" data-color-override="\${colorOverride}"></span>
		</div>
	</div>
</div>`;

var jstpl_eventCard = `<div class="bang-card \${extraClass} event" id="bang-card-\${uid}" data-id="\${id}" data-type="\${type}">
	<div class="card-front">
		<div class="card-name">\${name}</div>
	    <div class="card-background"></div>
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
                    <span class="card-copy-color" data-color="\${color}" data-color-override="\${colorOverride}"></span>
                    <span class="card-copy-color-override" data-color="\${color}" data-color-override="\${colorOverride}"></span>
				</div>
			</div>
		</div>
	</div>
	<p>
	\${text}
	</p>
</div>`;

var jstpl_eventCardTooltip = `<div class="bang-card-tooltip">
	<div class="bang-card-tooltip-sizing">
		<div class="bang-card event" id="bang-card-tooltip-\${id}" data-type="\${type}">
			<div class="card-front">
				<div class="card-name">\${name}</div>
				<div class="card-background"></div>
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

var jstpl_configPlayerBoard = `
<div class='player-board' id="player_board_config">
  <div id="player_config" class="player_board_content">
    <div id="player_config_row">
      <div id='help-icon'>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <g class="fa-group">
          <path class="fa-secondary" fill="currentColor" d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48zM224 430a46 46 0 1 1 46-46 46.06 46.06 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.81-25.27-33-45.7-33-27.19 0-39.44 13.13-57.3 35.79a12 12 0 0 1-16.67 2.13L116.82 170a12 12 0 0 1-2.71-16.26C141.4 113 176.16 90 230.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path>
          <path class="fa-primary" fill="currentColor" d="M224 338a46 46 0 1 0 46 46 46.05 46.05 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.66 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.67-32.54 34C215.13 238.53 184 254.94 184 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path>
        </g>
      </svg>
      </div>

      <div id="show-settings">
        <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
          <g>
            <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
            <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
          </g>
        </svg>
      </div>
    </div>
    <div class='settingsControlsHidden' id="settings-controls-container"></div>
  </div>
</div>
`;

var jstpl_noEvents = `
<div id="noEvents">
  <div id="xIcon">
      <svg fill="#000000" id="xIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 460.775 460.775" xml:space="preserve">
          <path d="M285.08,230.397L456.218,59.27c6.076-6.077,6.076-15.911,0-21.986L423.511,4.565c-2.913-2.911-6.866-4.55-10.992-4.55
  c-4.127,0-8.08,1.639-10.993,4.55l-171.138,171.14L59.25,4.565c-2.913-2.911-6.866-4.55-10.993-4.55
  c-4.126,0-8.08,1.639-10.992,4.55L4.558,37.284c-6.077,6.075-6.077,15.909,0,21.986l171.138,171.128L4.575,401.505
  c-6.074,6.077-6.074,15.911,0,21.986l32.709,32.719c2.911,2.911,6.865,4.55,10.992,4.55c4.127,0,8.08-1.639,10.994-4.55
  l171.117-171.12l171.118,171.12c2.913,2.911,6.866,4.55,10.993,4.55c4.128,0,8.081-1.639,10.992-4.55l32.709-32.719
  c6.074-6.075,6.074-15.909,0-21.986L285.08,230.397z"/>
      </svg>
  </div>
  <div id="noEventsLexeme">\${noEventsLexeme}</div>
</div>`;
</script>

{OVERALL_GAME_FOOTER}

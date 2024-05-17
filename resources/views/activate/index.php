<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPvian - Setup Tribe and Village</title>
    <link rel="stylesheet" href="/assets/css/activate.css">
</head>

<body>
<div class="background"></div>
<div class="container">
    <form id="character-form" action="/activate" method="POST">
        <input type="hidden" name="character" id="selected-character">

        <div class="tribe-section">
            <h1 class="title">Select your Tribe</h1>
            <p class="subtitle">Great empires begin with important decisions!</p>
            <p class="subtitle">Are you an attacker who loves to fight? Otherwise, save your time relatively, do you?</p>
            <p class="subtitle">Are you a team player who likes to develop a thriving economy to fire the fuse?</p>

            <div class="tribe-container">
                <div class="column">
                    <div class="character" id="teutons" data-tribe="teutons">
                        <h2 class="character-title">Teutons</h2>
                        <img src="/assets/images/activate/teutons.png" alt="teutons">
                        <div class="description">
                            <p class="properties">Properties:</p>
                            <ul>
                                <li>High time requirements.</li>
                                <li>Good for looting in the early game.</li>
                                <li>Powerful, cheap infantry.</li>
                                <li>For offensive players.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="character" id="gauls" data-tribe="gauls">
                        <h2 class="character-title">Gauls</h2>
                        <img src="/assets/images/activate/gauls.png" alt="gauls">
                        <div class="description">
                            <p class="properties">Properties:</p>
                            <ul>
                                <li>Low time requirements.</li>
                                <li>Loot protection and good defense.</li>
                                <li>Excellent, fast cavalry.</li>
                                <li>Very suitable for new players.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="character" id="romans" data-tribe="romans">
                        <h2 class="character-title">Romans</h2>
                        <img src="/assets/images/activate/romans.png" alt="romans">
                        <div class="description">
                            <p class="properties">Properties:</p>
                            <ul>
                                <li>Average time requirements.</li>
                                <li>Can improve villages in the fastest way.</li>
                                <li>Very strong but expensive military troops.</li>
                                <li>Hard to play for new players.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="location-section hidden">
            <h1 class="title">Choose start location</h1>
            <p class="subtitle">Where should I start building the empire that I want?</p>
            <p class="subtitle">Use the "recommended" area for the most suitable region,</p>
            <p class="subtitle">or choose the region where your friends are and create a team!</p>

            <div class="map-container">
                <div class="map" id="map">
                    <div class="sector" id="nw"></div>
                    <div class="sector" id="ne"></div>
                    <div class="sector" id="sw"></div>
                    <div class="sector" id="se"></div>
                </div>
                <div class="start">
                    <label for="sector-select"></label>
                    <select class="sector-select" name="sector" id="sector-select">
                        <option value="random">Random</option>
                        <option value="nw">North - West</option>
                        <option value="ne">North - East</option>
                        <option value="sw">South - West</option>
                        <option value="se">South - East</option>
                    </select>
                </div>
                <div class="buttons">
                    <button class="cancelb" type="button" id="go-back">Back</button>
                    <button class="submitb" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script src="/assets/js/activate.js"></script>
</body>

</html>

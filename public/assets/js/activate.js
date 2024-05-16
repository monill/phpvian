document.addEventListener('DOMContentLoaded', () => {

    const characters = document.querySelectorAll('.character');
    const selectedCharacterInput = document.getElementById('selected-character');
    const tribeSection = document.querySelector('.tribe-section'); // Changed getElementsByClassName to querySelector
    const locationSection = document.querySelector('.location-section'); // Changed getElementsByClassName to querySelector

    // Event listener to handle character selection
    characters.forEach(character => {
        character.addEventListener('click', () => {
            characters.forEach(c => {
                c.classList.remove('character-selected');
            });
            character.classList.add('character-selected');
        });
    });

    characters.forEach(character => {
        character.addEventListener('click', () => {
            selectedCharacterInput.value = character.getAttribute('data-tribe');
            tribeSection.style.display = 'none';
            locationSection.style.display = 'block';
        });
    });

    document.getElementById('go-back').addEventListener('click', () => {
        tribeSection.style.display = 'block';
        locationSection.style.display = 'none';
    });

    const sectors = document.querySelectorAll('.sector');
    const select = document.getElementById('sector-select');

    // Function to select a random option and update the map
    function selectRandomOption() {
        const options = Array.from(select.options);
        const randomIndex = Math.floor(Math.random() * (options.length - 1)) + 1; // Ensure it's not "random"
        const randomValue = options[randomIndex].value;
        select.value = randomValue;
        updateMapSelection(randomValue);
    }

    // Update map selection based on the select value
    function updateMapSelection(value) {
        clearSelection();
        if (value === 'random') {
            sectors.forEach(sector => {
                sector.classList.add('selected');
            });
        } else {
            document.getElementById(value).classList.add('selected');
        }
    }

    // Set initial selection to a random option
    selectRandomOption();

    sectors.forEach(sector => {
        sector.addEventListener('click', () => {
            clearSelection();
            sector.classList.add('selected');
            select.value = sector.id;
        });
    });

    select.addEventListener('change', () => {
        const value = select.value;
        updateMapSelection(value);
    });

    function clearSelection() {
        sectors.forEach(sector => {
            sector.classList.remove('selected');
        });
    }
});

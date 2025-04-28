<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Currency Converter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg">
    <h1 class="text-xl font-bold mb-6 text-center">Convert</h1>

    <div class="flex items-center border rounded-lg mb-6 overflow-hidden">
        <div class="flex items-center bg-white px-4 flex-shrink-0">
            <img id="base-flag" src="" alt="Base" class="w-6 h-6 mr-2">
            <span id="base-code" class="font-semibold"></span>
        </div>
        <input id="amount" type="number" value="1000" class="flex-1 p-4 focus:outline-none border-0 min-w-0">
        <button id="convert-btn" class="bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center p-4 rounded-r-lg transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h13M8 7l5-5m-5 5l5 5M16 17H3m13 0l-5 5m5-5l-5-5" />
            </svg>
        </button>
    </div>

    <!-- Spinner -->
    <div id="spinner" class="flex justify-center my-6 hidden">
        <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    <div id="currency-list" class="space-y-3"></div>
</div>

<script>
    const currencies = @json($currencies);
    const baseCurrency = currencies.find(c => c.is_base); // Get base
    let latestRates = {};

    document.addEventListener('DOMContentLoaded', () => {
        setupBaseCurrency();
        fetchRates(1000);

        document.getElementById('convert-btn').addEventListener('click', () => {
            const value = parseFloat(document.getElementById('amount').value) || 1;
            fetchRates(value);
        });
    });

    function setupBaseCurrency() {
        if (baseCurrency) {
            const countryCode = (baseCurrency.country_code || 'UN').toLowerCase();
            document.getElementById('base-flag').src = `https://flagcdn.com/w40/${countryCode}.png`;
            document.getElementById('base-code').textContent = baseCurrency.code;
        }
    }

    function fetchRates(amount) {
        const spinner = document.getElementById('spinner');
        const list = document.getElementById('currency-list');

        spinner.classList.remove('hidden');
        list.classList.add('opacity-50');

        fetch('/getRates') // <-- Fix here: add starting slash
            .then(response => response.json())
            .then(data => {
                latestRates = data;
                renderRates(amount);
            })
            .catch(error => console.error('Error fetching rates:', error))
            .finally(() => {
                spinner.classList.add('hidden');
                list.classList.remove('opacity-50');
            });
    }

    function renderRates(amount) {
        const list = document.getElementById('currency-list');
        list.innerHTML = '';

        currencies.forEach(curr => {
            if (curr.is_base) return; // Skip showing the base itself

            const code = curr.code;
            const countryCode = (curr.country_code || 'UN').toLowerCase();
            const flagUrl = `https://flagcdn.com/w40/${countryCode}.png`;
            const symbol = curr.symbol || '';

            const value = (latestRates[code] * amount || 0).toFixed(2);
            const rate = ((latestRates[code] || 0)).toFixed(4);

            const card = document.createElement('div');
            card.className = 'flex items-center justify-between bg-white p-4 rounded-lg border hover:bg-gray-50 transition transform hover:scale-[1.01] duration-200';

            card.innerHTML = `
                <div class="flex items-center space-x-3">
                    <img src="${flagUrl}" alt="${code}" class="w-8 h-8 rounded-full">
                    <div>
                        <div class="font-bold">${code}</div>
                        <div class="text-gray-500 text-xs">1 ${baseCurrency.code} = ${rate} ${code}</div>
                    </div>
                </div>
                <div class="font-bold text-lg">${symbol}${value}</div>
            `;

            list.appendChild(card);
        });
    }
</script>

</body>
</html>

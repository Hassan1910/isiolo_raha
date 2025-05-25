<!DOCTYPE html>
<html>
<head>
    <title>Paystack Currency Test</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        button {
            background-color: #0a2e5c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .currency-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Paystack Currency Test</h1>
    <p>This page tests different currencies with Paystack to find which ones are supported by your account.</p>

    <div class="card">
        <h2>Test with Different Currencies</h2>
        <p>Click on a currency to test if it works with your Paystack account:</p>

        <div class="currency-grid">
            <button onclick="testCurrency('NGN')">Nigerian Naira (NGN)</button>
            <button onclick="testCurrency('USD')">US Dollar (USD)</button>
            <button onclick="testCurrency('GHS')">Ghanaian Cedi (GHS)</button>
            <button onclick="testCurrency('ZAR')">South African Rand (ZAR)</button>
            <button onclick="testCurrency('KES')" style="background-color: #4CAF50; font-weight: bold;">Kenyan Shilling (KES) - Your Account Currency</button>
            <button onclick="testCurrency('XOF')">West African CFA (XOF)</button>
            <button onclick="testCurrency('EGP')">Egyptian Pound (EGP)</button>
            <button onclick="testCurrency('GBP')">British Pound (GBP)</button>
            <button onclick="testCurrency('EUR')">Euro (EUR)</button>
            <button onclick="testCurrency('')">Default Currency</button>
        </div>
    </div>

    <div class="card">
        <h2>Debug Output</h2>
        <pre id="debug-output">Click a currency button to test...</pre>
    </div>

    <!-- Load Paystack JS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>

    <script>
        const debugOutput = document.getElementById('debug-output');

        // Log initial debug info
        debugOutput.textContent = 'Debug Info:\n';
        debugOutput.textContent += '- Browser: ' + navigator.userAgent + '\n';
        debugOutput.textContent += '- Paystack JS loaded: ' + (typeof PaystackPop !== 'undefined') + '\n';

        function testCurrency(currency) {
            debugOutput.textContent += '\nTesting currency: ' + (currency || 'Default') + '\n';

            try {
                const config = {
                    key: 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b',
                    email: 'test@example.com',
                    amount: 10000, // 100 in the lowest currency denomination
                    callback: function(response) {
                        debugOutput.textContent += '- Payment successful: ' + JSON.stringify(response) + '\n';
                    },
                    onClose: function() {
                        debugOutput.textContent += '- Payment window closed\n';
                    }
                };

                // Only add currency if it's not empty
                if (currency) {
                    config.currency = currency;
                }

                debugOutput.textContent += '- Configuration: ' + JSON.stringify(config, null, 2) + '\n';

                const handler = PaystackPop.setup(config);
                debugOutput.textContent += '- Handler created successfully\n';
                handler.openIframe();
            } catch (error) {
                debugOutput.textContent += '- Error: ' + error.message + '\n';
                console.error('Error initializing Paystack:', error);
            }
        }
    </script>
</body>
</html>

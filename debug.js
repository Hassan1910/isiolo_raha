// Add this to the head of your HTML files to debug QR code issues
console.log('QR code debugging script loaded');

// Check if QRCode library is available
window.addEventListener('load', function() {
    console.log('Window loaded');
    
    // Check if QRCode is defined
    if (typeof QRCode === 'undefined') {
        console.error('QRCode library is not loaded!');
    } else {
        console.log('QRCode library is loaded successfully');
    }
    
    // Check if the QR code container exists
    const qrcodeElement = document.getElementById('qrcode');
    if (!qrcodeElement) {
        console.error('QR code container element not found!');
    } else {
        console.log('QR code container found:', qrcodeElement);
        
        // Try to generate QR code
        try {
            const qr = new QRCode(qrcodeElement, {
                text: 'https://example.com',
                width: 160,
                height: 160,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            console.log('QR code generated with test URL');
        } catch (error) {
            console.error('Error generating QR code:', error);
        }
    }
});

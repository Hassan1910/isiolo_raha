<?php
// Set page title
$page_title = "About Us";

// Include header
require_once 'includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="bg-primary-700 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">About Isiolo Raha</h1>
        <p class="text-xl max-w-2xl mx-auto">
            Your trusted partner for comfortable and reliable bus travel across Kenya.
        </p>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-4">Our Story</h2>
                <p class="text-gray-600 mb-4">
                    Founded in 2005, Isiolo Raha has grown from a small local operator to one of Kenya's most trusted bus companies. 
                    Our journey began with just two buses serving the Nairobi-Isiolo route, and today we proudly operate a fleet of 
                    modern buses connecting major cities and towns across Kenya.
                </p>
                <p class="text-gray-600 mb-4">
                    Our name "Isiolo Raha" combines our roots in Isiolo with the Swahili word "Raha," meaning comfort and joy - 
                    perfectly capturing our commitment to providing comfortable and enjoyable travel experiences for all our passengers.
                </p>
                <p class="text-gray-600">
                    Over the years, we have continuously invested in modern vehicles, trained our staff to the highest standards, 
                    and embraced technology to enhance our services. Our new online booking system represents our latest step in 
                    making bus travel more convenient and accessible for everyone.
                </p>
            </div>
            <div class="rounded-lg overflow-hidden shadow-lg">
                <img src="assets/images/about-bus.jpg" alt="Isiolo Raha Bus" class="w-full h-auto">
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold">Our Mission & Vision</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Mission -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bullseye text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-4 text-center">Our Mission</h3>
                <p class="text-gray-600 text-center">
                    To provide safe, reliable, and comfortable transportation services that exceed customer expectations 
                    while maintaining the highest standards of professionalism and integrity in all our operations.
                </p>
            </div>
            
            <!-- Vision -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-eye text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-4 text-center">Our Vision</h3>
                <p class="text-gray-600 text-center">
                    To be the leading bus transportation company in East Africa, recognized for excellence in service, 
                    innovation, and commitment to sustainable practices that benefit our customers, employees, and communities.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold">Our Core Values</h2>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                These principles guide our decisions and actions every day as we serve our customers.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Safety -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Safety First</h3>
                <p class="text-gray-600 text-center">
                    We prioritize the safety of our passengers, staff, and other road users above all else. 
                    Our vehicles undergo regular maintenance, and our drivers receive continuous training.
                </p>
            </div>
            
            <!-- Reliability -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Reliability</h3>
                <p class="text-gray-600 text-center">
                    We understand the importance of punctuality and consistency. Our schedules are designed 
                    to ensure timely departures and arrivals, respecting our customers' time.
                </p>
            </div>
            
            <!-- Customer Focus -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Customer Focus</h3>
                <p class="text-gray-600 text-center">
                    Our customers are at the heart of everything we do. We continuously seek feedback 
                    and improve our services to meet and exceed their expectations.
                </p>
            </div>
            
            <!-- Integrity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-handshake text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Integrity</h3>
                <p class="text-gray-600 text-center">
                    We conduct our business with honesty, transparency, and ethical standards. 
                    We believe in building trust through our actions and accountability.
                </p>
            </div>
            
            <!-- Innovation -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-lightbulb text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Innovation</h3>
                <p class="text-gray-600 text-center">
                    We embrace technology and new ideas to enhance our services and operations. 
                    Our online booking system is just one example of our commitment to innovation.
                </p>
            </div>
            
            <!-- Community -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-hands-helping text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 text-center">Community</h3>
                <p class="text-gray-600 text-center">
                    We are committed to giving back to the communities we serve through various 
                    initiatives and by providing employment opportunities.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Fleet Section -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold">Our Fleet</h2>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                We operate a modern fleet of well-maintained buses to ensure your journey is comfortable and safe.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Standard Bus -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="h-48 bg-gray-300">
                    <img src="assets/images/standard-bus.jpg" alt="Standard Bus" class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Standard Buses</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> 44 comfortable seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Air conditioning</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Reclining seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Onboard entertainment</li>
                    </ul>
                </div>
            </div>
            
            <!-- Executive Bus -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="h-48 bg-gray-300">
                    <img src="assets/images/executive-bus.jpg" alt="Executive Bus" class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Executive Buses</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> 40 spacious seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Air conditioning</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Reclining seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> WiFi & USB charging</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Onboard entertainment</li>
                    </ul>
                </div>
            </div>
            
            <!-- Luxury Bus -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="h-48 bg-gray-300">
                    <img src="assets/images/luxury-bus.jpg" alt="Luxury Bus" class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Luxury Buses</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> 36 premium seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Air conditioning</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Extra-wide reclining seats</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> WiFi & USB charging</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Onboard entertainment</li>
                        <li class="flex items-center"><i class="fas fa-check text-primary-600 mr-2"></i> Complimentary refreshments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-12 bg-primary-700 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Experience the Isiolo Raha Difference</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Book your next journey with us and discover why thousands of travelers choose Isiolo Raha every day.
        </p>
        <a href="index.php#search-form" class="bg-white text-primary-700 hover:bg-gray-100 font-bold py-3 px-6 rounded-md inline-block transition duration-300 ease-in-out">
            Book Your Trip Now
        </a>
    </div>
</section>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>

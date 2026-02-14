<footer class="bg-gray-800 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">Hostel Manager</h3>
                <p class="text-gray-400">Professional hostel management system for seamless room booking.</p>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ url('/') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Register</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Contact</h3>
                <p class="text-gray-400">
                    Email: info@hostelmanager.com<br>
                    Phone: +1 (555) 123-4567
                </p>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; 2026 Hostel Management System. All rights reserved.</p>
        </div>
    </div>
</footer>

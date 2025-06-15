-- Sample data for EventZon XAMPP version
-- Run this after installation to populate with demo events

-- Insert sample admin user (password: admin123)
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@eventzon.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample events
INSERT IGNORE INTO events (title, description, date, venue, price, available_tickets, category, city, region, organizer_id) VALUES 
('Cameroon Music Festival 2025', 'Join us for an unforgettable night of traditional and modern Cameroon music featuring top artists from across the country. Experience the rich cultural heritage of Cameroon through music, dance, and food.', '2025-07-15 19:00:00', 'Palais des Sports de Yaounde', 5000, 500, 'music', 'Yaounde', 'Centre', 1),

('Digital Innovation Summit', 'The premier technology conference in Central Africa bringing together entrepreneurs, developers, and investors to discuss the future of technology in Africa. Network with industry leaders and discover the latest innovations.', '2025-08-20 09:00:00', 'Hilton Hotel Douala', 15000, 200, 'technology', 'Douala', 'Littoral', 1),

('Business Leadership Conference', 'Learn from successful business leaders and entrepreneurs who have built thriving companies in Cameroon and across Africa. Gain insights into leadership, strategy, and business growth.', '2025-09-10 08:30:00', 'Centre de Conferences de Yaounde', 25000, 150, 'business', 'Yaounde', 'Centre', 1),

('Contemporary Art Exhibition', 'Discover the vibrant world of contemporary Cameroonian art featuring works by emerging and established artists. Experience paintings, sculptures, and installations that tell the story of modern Cameroon.', '2025-07-30 10:00:00', 'Centre Culturel Francais', 2000, 300, 'arts', 'Douala', 'Littoral', 1),

('Cameroon Food Festival', 'Celebrate the rich culinary heritage of Cameroon with traditional dishes from all ten regions. Enjoy cooking demonstrations, tastings, and cultural performances in a family-friendly environment.', '2025-08-05 11:00:00', 'Place du Gouvernement', 3000, 1000, 'food', 'Buea', 'Southwest', 1),

('Lions Football Championship', 'Cheer for the Indomitable Lions in this exciting football championship featuring teams from across Cameroon. Experience the passion and energy of Cameroonian football culture.', '2025-09-25 16:00:00', 'Stade Omnisport Ahmadou Ahidjo', 8000, 30000, 'sports', 'Yaounde', 'Centre', 1),

('Jazz Night Bamenda', 'An intimate evening of smooth jazz featuring local and international artists. Enjoy great music, fine dining, and a sophisticated atmosphere in the heart of Bamenda.', '2025-08-12 20:00:00', 'Ayaba Hotel Bamenda', 4000, 100, 'music', 'Bamenda', 'Northwest', 1),

('Tech Startup Pitch Competition', 'Watch innovative startups pitch their ideas to a panel of investors and industry experts. Discover the next generation of tech companies emerging from Cameroon.', '2025-07-28 14:00:00', 'University of Buea Auditorium', 1000, 250, 'technology', 'Buea', 'Southwest', 1),

('Traditional Dance Festival', 'Experience the rich diversity of Cameroonian traditional dances with performances from all regions. Learn about the cultural significance and history behind each dance style.', '2025-09-15 17:00:00', 'Maroua Cultural Center', 1500, 800, 'arts', 'Maroua', 'Far North', 1),

('Cameroon Wine Tasting', 'Discover the emerging wine industry in Cameroon with tastings of locally produced wines. Learn about viticulture in tropical climates and meet local winemakers.', '2025-08-18 18:30:00', 'Mount Cameroon Hotel', 7500, 80, 'food', 'Limbe', 'Southwest', 1);
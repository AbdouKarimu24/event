-- Insert sample users (including admin)
INSERT INTO users (email, name, password, role) VALUES 
('admin@eventzon.cm', 'EventZon Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('organizer1@cameroon.cm', 'Cameroon Cultural Center', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('organizer2@yaounde.cm', 'Yaounde Events Ltd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('organizer3@douala.cm', 'Douala Business Hub', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert 10 authentic events from Cameroon
INSERT INTO events (title, description, date, venue, price, available_tickets, category, city, region, image_url, organizer_id) VALUES

('Festival Ngondo 2024', 'The annual traditional festival of the Sawa people celebrating water spirits and cultural heritage. Features traditional dances, canoe races, and cultural exhibitions along the Wouri River.', '2024-12-15 10:00:00', 'Rive Wouri, Douala', 5000, 2000, 'arts', 'Douala', 'Littoral', 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3', 2),

('Cameroon Tech Summit 2024', 'Leading technology conference bringing together innovators, entrepreneurs, and tech leaders across Central Africa. Featuring keynotes on AI, fintech, and digital transformation.', '2024-11-20 09:00:00', 'Hilton Yaounde', 25000, 500, 'technology', 'Yaounde', 'Centre', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87', 3),

('Bamenda Ring Road Cultural Festival', 'Celebration of the diverse cultures of the Northwest Region featuring traditional music, dance, and local crafts from the Grassfields communities.', '2024-10-25 14:00:00', 'Bamenda Commercial Avenue', 3000, 1500, 'arts', 'Bamenda', 'Northwest', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d', 1),

('Cameroon Coffee & Cocoa Expo', 'International trade exhibition showcasing Cameroons premium coffee and cocoa products. Network with farmers, traders, and international buyers.', '2024-11-10 08:00:00', 'Palais des Congrès, Yaounde', 15000, 800, 'business', 'Yaounde', 'Centre', 'https://images.unsplash.com/photo-1447933601403-0c6688de566e', 3),

('Mount Cameroon Race of Hope', 'Annual mountain race to the summit of Mount Cameroon, West Africas highest peak. International athletics event with categories for professionals and amateurs.', '2024-02-10 06:00:00', 'Buea Mountain Club', 12000, 300, 'sports', 'Buea', 'Southwest', 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256', 1),

('Kribi Jazz Festival', 'International jazz festival on the beautiful Atlantic coast featuring local and international artists. Three days of music, food, and beach activities.', '2024-12-28 18:00:00', 'Kribi Beach Resort', 8000, 1200, 'music', 'Kribi', 'South', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f', 2),

('Bafoussam Agribusiness Conference', 'Regional conference focusing on agricultural innovation and agribusiness opportunities in the Western Highlands. Features farmer cooperatives and modern farming techniques.', '2024-09-15 09:00:00', 'Centre de Conférences Bafoussam', 10000, 600, 'business', 'Bafoussam', 'West', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b', 3),

('Limbe Festival of Arts and Culture', 'Coastal cultural festival celebrating the artistic heritage of the Southwest Region. Traditional art exhibitions, music performances, and cultural workshops.', '2024-08-17 16:00:00', 'Limbe Botanic Garden', 4000, 1000, 'arts', 'Limbe', 'Southwest', 'https://images.unsplash.com/photo-1471919743851-c4df8b6ee133', 1),

('Garoua Business Forum', 'Northern Cameroons premier business networking event connecting entrepreneurs, investors, and government officials. Focus on cross-border trade and regional development.', '2024-10-05 08:30:00', 'Hôtel Relais Saint-Hubert', 20000, 400, 'business', 'Garoua', 'North', 'https://images.unsplash.com/photo-1515187029135-18ee286d815b', 3),

('Makossa Music Festival Douala', 'Celebration of Cameroons iconic Makossa music genre featuring legendary artists and new talent. Three-day festival with concerts, workshops, and music history exhibitions.', '2024-07-14 19:00:00', 'Palais des Sports de Douala', 7500, 3000, 'music', 'Douala', 'Littoral', 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a', 2);
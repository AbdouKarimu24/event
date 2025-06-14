import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, Calendar, MapPin, Music, Briefcase, Laptop, Palette, Trophy, Utensils } from "lucide-react";

export default function Landing() {
  const categories = [
    { id: "music", name: "Music", icon: Music },
    { id: "business", name: "Business", icon: Briefcase },
    { id: "technology", name: "Technology", icon: Laptop },
    { id: "arts", name: "Arts & Culture", icon: Palette },
    { id: "sports", name: "Sports", icon: Trophy },
    { id: "food", name: "Food & Drink", icon: Utensils },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-background to-muted">
      {/* Header */}
      <header className="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-primary">EventZon</h1>
            </div>
            <Button 
              onClick={() => window.location.href = "/api/login"}
              className="bg-primary hover:bg-primary/90"
            >
              Sign In
            </Button>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-4xl md:text-6xl font-bold mb-6">Discover Amazing Events</h2>
            <p className="text-xl md:text-2xl mb-8 opacity-90">Find and book the best events happening around you</p>
            
            {/* Enhanced Search */}
            <Card className="max-w-4xl mx-auto bg-white text-gray-800 shadow-lg">
              <CardContent className="p-6">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Event Name</label>
                    <Input placeholder="What are you looking for?" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <Input placeholder="Where?" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <Input type="date" />
                  </div>
                  <div className="flex items-end">
                    <Button className="w-full bg-primary text-white hover:bg-primary/90">
                      <Search className="w-4 h-4 mr-2" />
                      Search Events
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Event Categories */}
      <section className="py-8 bg-white border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-wrap gap-4 justify-center">
            {categories.map((category) => {
              const IconComponent = category.icon;
              return (
                <Button
                  key={category.id}
                  variant="outline"
                  className="flex items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-primary hover:text-white rounded-full transition-colors border-none"
                >
                  <IconComponent className="w-4 h-4" />
                  <span>{category.name}</span>
                </Button>
              );
            })}
          </div>
        </div>
      </section>

      {/* Sample Events Preview */}
      <main className="py-12 bg-light">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-8">
            <h3 className="text-3xl font-bold text-gray-900 mb-4">Popular Events</h3>
            <p className="text-gray-600 mb-6">Sign in to discover and book amazing events</p>
            <Button 
              onClick={() => window.location.href = "/api/login"}
              size="lg"
              className="bg-primary hover:bg-primary/90"
            >
              Get Started
            </Button>
          </div>

          {/* Sample Event Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 opacity-75">
            {[1, 2, 3].map((i) => (
              <Card key={i} className="overflow-hidden hover:shadow-lg transition-shadow">
                <div className="h-48 bg-gradient-to-r from-primary/20 to-secondary/20 flex items-center justify-center">
                  <div className="text-center">
                    <Calendar className="w-12 h-12 mx-auto mb-2 text-primary" />
                    <p className="text-gray-600">Sample Event {i}</p>
                  </div>
                </div>
                <CardContent className="p-4">
                  <div className="flex items-center justify-between mb-2">
                    <span className="inline-block bg-primary text-white text-xs px-2 py-1 rounded-full">
                      Category
                    </span>
                  </div>
                  <h4 className="font-semibold text-lg text-gray-900 mb-2">Sample Event Title</h4>
                  <div className="space-y-1 text-sm text-gray-600 mb-3">
                    <div className="flex items-center">
                      <Calendar className="w-4 h-4 text-primary mr-2" />
                      <span>July 15, 2024</span>
                    </div>
                    <div className="flex items-center">
                      <MapPin className="w-4 h-4 text-primary mr-2" />
                      <span>Sample Location</span>
                    </div>
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="text-lg font-bold text-primary">$45</div>
                    <Button size="sm" disabled className="opacity-50">
                      Sign In to Book
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-dark text-white py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
              <h3 className="text-xl font-bold mb-4">EventZon</h3>
              <p className="text-gray-300 mb-4">Discover and book amazing events happening around the world.</p>
            </div>
            <div>
              <h4 className="font-semibold mb-4">For Attendees</h4>
              <ul className="space-y-2 text-gray-300">
                <li><a href="#" className="hover:text-white">Browse Events</a></li>
                <li><a href="#" className="hover:text-white">My Bookings</a></li>
                <li><a href="#" className="hover:text-white">Help Center</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">For Organizers</h4>
              <ul className="space-y-2 text-gray-300">
                <li><a href="#" className="hover:text-white">Create Event</a></li>
                <li><a href="#" className="hover:text-white">Manage Events</a></li>
                <li><a href="#" className="hover:text-white">Analytics</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Company</h4>
              <ul className="space-y-2 text-gray-300">
                <li><a href="#" className="hover:text-white">About Us</a></li>
                <li><a href="#" className="hover:text-white">Contact</a></li>
                <li><a href="#" className="hover:text-white">Privacy Policy</a></li>
                <li><a href="#" className="hover:text-white">Terms of Service</a></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
            <p>&copy; 2024 EventZon. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

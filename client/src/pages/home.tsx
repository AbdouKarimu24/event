import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Music, Briefcase, Laptop, Palette, Trophy, Utensils, Grid, List } from "lucide-react";
import Header from "@/components/header";
import EventCard from "@/components/event-card";
import Footer from "@/components/footer";
import CartSidebar from "@/components/cart-sidebar";
import type { EventWithOrganizer } from "@shared/schema";

export default function Home() {
  const [searchFilters, setSearchFilters] = useState({
    name: "",
    location: "",
    date: "",
    category: "",
    sortBy: "date" as "date" | "price" | "popularity",
  });
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [isCartOpen, setIsCartOpen] = useState(false);

  const { data: events = [], isLoading } = useQuery<EventWithOrganizer[]>({
    queryKey: ["/api/events", searchFilters],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (searchFilters.name) params.append("search", searchFilters.name);
      if (searchFilters.location) params.append("city", searchFilters.location);
      if (searchFilters.date) params.append("date", searchFilters.date);
      if (searchFilters.category) params.append("category", searchFilters.category);
      if (searchFilters.sortBy) params.append("sortBy", searchFilters.sortBy);
      
      const response = await fetch(`/api/events?${params}`);
      if (!response.ok) throw new Error("Failed to fetch events");
      return response.json();
    },
  });

  const categories = [
    { id: "music", name: "Music", icon: Music },
    { id: "business", name: "Business", icon: Briefcase },
    { id: "technology", name: "Technology", icon: Laptop },
    { id: "arts", name: "Arts & Culture", icon: Palette },
    { id: "sports", name: "Sports", icon: Trophy },
    { id: "food", name: "Food & Drink", icon: Utensils },
  ];

  const handleSearch = () => {
    // Query will automatically refetch due to dependency on searchFilters
  };

  const handleCategoryFilter = (category: string) => {
    setSearchFilters(prev => ({
      ...prev,
      category: prev.category === category ? "" : category
    }));
  };

  return (
    <>
      <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
      
      {/* Hero Section */}
      <section className="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-4xl md:text-6xl font-bold mb-6">Discover Amazing Events</h2>
            <p className="text-xl md:text-2xl mb-8 opacity-90">Find and book the best events happening around you</p>
            
            {/* Enhanced Search */}
            <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6 text-gray-800">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Event Name</label>
                  <Input
                    placeholder="What are you looking for?"
                    value={searchFilters.name}
                    onChange={(e) => setSearchFilters(prev => ({ ...prev, name: e.target.value }))}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                  <Input
                    placeholder="Where?"
                    value={searchFilters.location}
                    onChange={(e) => setSearchFilters(prev => ({ ...prev, location: e.target.value }))}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                  <Input
                    type="date"
                    value={searchFilters.date}
                    onChange={(e) => setSearchFilters(prev => ({ ...prev, date: e.target.value }))}
                  />
                </div>
                <div className="flex items-end">
                  <Button 
                    onClick={handleSearch}
                    className="w-full bg-primary text-white hover:bg-primary/90"
                  >
                    Search Events
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Event Categories */}
      <section className="py-8 bg-white border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-wrap gap-4 justify-center">
            {categories.map((category) => {
              const IconComponent = category.icon;
              const isActive = searchFilters.category === category.id;
              return (
                <Button
                  key={category.id}
                  onClick={() => handleCategoryFilter(category.id)}
                  className={`flex items-center space-x-2 px-4 py-2 rounded-full transition-colors border-none ${
                    isActive 
                      ? "bg-primary text-white hover:bg-primary/90" 
                      : "bg-gray-100 text-gray-700 hover:bg-primary hover:text-white"
                  }`}
                >
                  <IconComponent className="w-4 h-4" />
                  <span>{category.name}</span>
                </Button>
              );
            })}
          </div>
        </div>
      </section>

      {/* Event Listings */}
      <main className="py-12 bg-light">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Section Header */}
          <div className="flex justify-between items-center mb-8">
            <div>
              <h3 className="text-3xl font-bold text-gray-900">Upcoming Events</h3>
              <p className="text-gray-600 mt-2">
                {isLoading ? "Loading events..." : `${events.length} events found`}
              </p>
            </div>
            <div className="flex items-center space-x-4">
              <Select 
                value={searchFilters.sortBy}
                onValueChange={(value: "date" | "price" | "popularity") => 
                  setSearchFilters(prev => ({ ...prev, sortBy: value }))
                }
              >
                <SelectTrigger className="w-48">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="date">Sort by Date</SelectItem>
                  <SelectItem value="price">Sort by Price</SelectItem>
                  <SelectItem value="popularity">Sort by Popularity</SelectItem>
                </SelectContent>
              </Select>
              <div className="flex bg-gray-100 rounded-md p-1">
                <Button
                  size="sm"
                  variant="ghost"
                  onClick={() => setViewMode("grid")}
                  className={`p-2 rounded ${viewMode === "grid" ? "bg-white shadow-sm text-primary" : "text-gray-400"}`}
                >
                  <Grid className="w-4 h-4" />
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  onClick={() => setViewMode("list")}
                  className={`p-2 rounded ${viewMode === "list" ? "bg-white shadow-sm text-primary" : "text-gray-400"}`}
                >
                  <List className="w-4 h-4" />
                </Button>
              </div>
            </div>
          </div>

          {/* Event Grid */}
          {isLoading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {[...Array(8)].map((_, i) => (
                <div key={i} className="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
                  <div className="w-full h-48 bg-gray-200"></div>
                  <div className="p-4 space-y-3">
                    <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div className="h-6 bg-gray-200 rounded w-full"></div>
                    <div className="space-y-2">
                      <div className="h-4 bg-gray-200 rounded w-2/3"></div>
                      <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                    <div className="flex justify-between items-center">
                      <div className="h-6 bg-gray-200 rounded w-16"></div>
                      <div className="h-8 bg-gray-200 rounded w-24"></div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : events.length === 0 ? (
            <div className="text-center py-16">
              <h4 className="text-xl font-semibold text-gray-900 mb-2">No events found</h4>
              <p className="text-gray-600">Try adjusting your search filters to find more events.</p>
            </div>
          ) : (
            <div className={`grid gap-6 ${
              viewMode === "grid" 
                ? "grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" 
                : "grid-cols-1"
            }`}>
              {events.map((event) => (
                <EventCard key={event.id} event={event} viewMode={viewMode} />
              ))}
            </div>
          )}
        </div>
      </main>

      <Footer />
      <CartSidebar isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </>
  );
}

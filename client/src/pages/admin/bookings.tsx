import { useState, useEffect } from "react";
import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { Calendar, Clock, MapPin, User, Mail, Phone, Search, Download, Filter } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/hooks/useAuth";
import Header from "@/components/header";
import Footer from "@/components/footer";
import CartSidebar from "@/components/cart-sidebar";
import type { BookingWithEvent } from "@shared/schema";

export default function AdminBookings() {
  const { toast } = useToast();
  const { isAuthenticated, isLoading: authLoading, user } = useAuth();
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [eventFilter, setEventFilter] = useState("all");

  const { data: bookings = [], isLoading } = useQuery<BookingWithEvent[]>({
    queryKey: ["/api/admin/bookings"],
    enabled: !!isAuthenticated && user?.role === "admin",
  });

  // Redirect to login if not authenticated or not admin
  useEffect(() => {
    if (!authLoading && (!isAuthenticated || user?.role !== "admin")) {
      toast({
        title: "Access Denied",
        description: "You need admin access to view this page.",
        variant: "destructive",
      });
      setTimeout(() => {
        window.location.href = "/api/login";
      }, 500);
      return;
    }
  }, [isAuthenticated, authLoading, user, toast]);

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-US", { 
      month: "short", 
      day: "numeric",
      year: "numeric"
    });
  };

  const formatTime = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(":");
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
    });
  };

  const formatDateTime = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleString("en-US", { 
      month: "short", 
      day: "numeric",
      year: "numeric",
      hour: "numeric",
      minute: "2-digit",
      hour12: true
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "confirmed":
        return "bg-green-100 text-green-800 border-green-200";
      case "cancelled":
        return "bg-red-100 text-red-800 border-red-200";
      default:
        return "bg-gray-100 text-gray-800 border-gray-200";
    }
  };

  // Get unique events for filter
  const uniqueEvents = Array.from(
    new Set(bookings.map(booking => booking.event.id))
  ).map(eventId => {
    const booking = bookings.find(b => b.event.id === eventId);
    return booking?.event;
  }).filter(Boolean);

  // Filter bookings
  const filteredBookings = bookings.filter(booking => {
    const matchesSearch = 
      booking.attendeeName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      booking.attendeeEmail.toLowerCase().includes(searchTerm.toLowerCase()) ||
      booking.bookingReference?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      booking.event.title.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesStatus = statusFilter === "all" || booking.status === statusFilter;
    const matchesEvent = eventFilter === "all" || booking.event.id.toString() === eventFilter;
    
    return matchesSearch && matchesStatus && matchesEvent;
  });

  // Calculate stats
  const totalBookings = bookings.length;
  const confirmedBookings = bookings.filter(b => b.status === "confirmed").length;
  const totalRevenue = bookings
    .filter(b => b.status === "confirmed")
    .reduce((sum, b) => sum + parseFloat(b.totalAmount), 0);

  if (authLoading || isLoading) {
    return (
      <>
        <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
        <div className="min-h-screen bg-light">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div className="animate-pulse">
              <div className="h-8 bg-gray-200 rounded w-48 mb-6"></div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {[...Array(3)].map((_, i) => (
                  <div key={i} className="h-32 bg-gray-200 rounded-lg"></div>
                ))}
              </div>
              <div className="space-y-4">
                {[...Array(6)].map((_, i) => (
                  <div key={i} className="h-40 bg-gray-200 rounded-lg"></div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
      
      <div className="min-h-screen bg-light">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* Header */}
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-2">Booking Management</h1>
            <p className="text-gray-600">View and manage all event bookings</p>
          </div>

          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <Calendar className="h-8 w-8 text-primary" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Total Bookings</p>
                    <p className="text-2xl font-bold text-gray-900">{totalBookings}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <User className="h-8 w-8 text-green-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Confirmed Bookings</p>
                    <p className="text-2xl font-bold text-gray-900">{confirmedBookings}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <Download className="h-8 w-8 text-blue-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p className="text-2xl font-bold text-gray-900">${totalRevenue.toFixed(2)}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Filters */}
          <Card className="mb-6">
            <CardContent className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                  <Input
                    placeholder="Search bookings..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
                
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger>
                    <SelectValue placeholder="Filter by status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="confirmed">Confirmed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
                
                <Select value={eventFilter} onValueChange={setEventFilter}>
                  <SelectTrigger>
                    <SelectValue placeholder="Filter by event" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Events</SelectItem>
                    {uniqueEvents.map((event) => (
                      <SelectItem key={event!.id} value={event!.id.toString()}>
                        {event!.title}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                
                <Button variant="outline" className="w-full">
                  <Download className="w-4 h-4 mr-2" />
                  Export CSV
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* Bookings List */}
          {filteredBookings.length === 0 ? (
            <Card>
              <CardContent className="p-12 text-center">
                <Calendar className="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <h3 className="text-xl font-semibold text-gray-900 mb-2">No bookings found</h3>
                <p className="text-gray-600">
                  {bookings.length === 0 
                    ? "No bookings have been made yet." 
                    : "Try adjusting your search or filter criteria."
                  }
                </p>
              </CardContent>
            </Card>
          ) : (
            <div className="space-y-4">
              {filteredBookings.map((booking) => (
                <Card key={booking.id} className="hover:shadow-lg transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between">
                      <div className="flex items-start space-x-4 flex-1">
                        <div className="w-16 h-16 bg-gradient-to-r from-primary/20 to-secondary/20 rounded-lg flex items-center justify-center flex-shrink-0">
                          {booking.event.imageUrl ? (
                            <img 
                              src={booking.event.imageUrl}
                              alt={booking.event.title}
                              className="w-full h-full object-cover rounded-lg"
                            />
                          ) : (
                            <Calendar className="w-6 h-6 text-primary" />
                          )}
                        </div>
                        
                        <div className="flex-1 min-w-0">
                          <div className="flex items-start justify-between mb-3">
                            <div>
                              <h3 className="text-lg font-semibold text-gray-900 mb-1">
                                {booking.event.title}
                              </h3>
                              <p className="text-sm text-gray-600">
                                Booking #{booking.bookingReference}
                              </p>
                            </div>
                            <Badge className={`${getStatusColor(booking.status)} border`}>
                              {booking.status}
                            </Badge>
                          </div>
                          
                          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                            {/* Event Details */}
                            <div className="space-y-2 text-sm text-gray-600">
                              <h4 className="font-medium text-gray-900">Event Details</h4>
                              <div className="flex items-center">
                                <Calendar className="w-4 h-4 mr-2 text-primary" />
                                <span>{formatDate(booking.event.eventDate)}</span>
                              </div>
                              {booking.event.startTime && (
                                <div className="flex items-center">
                                  <Clock className="w-4 h-4 mr-2 text-primary" />
                                  <span>{formatTime(booking.event.startTime)}</span>
                                </div>
                              )}
                              <div className="flex items-center">
                                <MapPin className="w-4 h-4 mr-2 text-primary" />
                                <span className="truncate">{booking.event.venue}</span>
                              </div>
                            </div>
                            
                            {/* Attendee Details */}
                            <div className="space-y-2 text-sm text-gray-600">
                              <h4 className="font-medium text-gray-900">Attendee Details</h4>
                              <div className="flex items-center">
                                <User className="w-4 h-4 mr-2 text-primary" />
                                <span>{booking.attendeeName}</span>
                              </div>
                              <div className="flex items-center">
                                <Mail className="w-4 h-4 mr-2 text-primary" />
                                <span className="truncate">{booking.attendeeEmail}</span>
                              </div>
                              {booking.attendeePhone && (
                                <div className="flex items-center">
                                  <Phone className="w-4 h-4 mr-2 text-primary" />
                                  <span>{booking.attendeePhone}</span>
                                </div>
                              )}
                            </div>
                          </div>
                          
                          <div className="flex items-center justify-between pt-3 border-t">
                            <div className="flex items-center space-x-6 text-sm">
                              <div>
                                <span className="text-gray-600">Quantity:</span>
                                <span className="ml-1 font-medium">{booking.quantity} tickets</span>
                              </div>
                              <div>
                                <span className="text-gray-600">Booked:</span>
                                <span className="ml-1 font-medium">{formatDateTime(booking.createdAt)}</span>
                              </div>
                              <div className="text-lg font-bold text-primary">
                                ${parseFloat(booking.totalAmount).toFixed(2)}
                              </div>
                            </div>
                            
                            <div className="flex items-center space-x-2">
                              <Button variant="outline" size="sm">
                                <Mail className="w-4 h-4 mr-2" />
                                Contact
                              </Button>
                              <Button variant="outline" size="sm">
                                <Download className="w-4 h-4 mr-2" />
                                Receipt
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </div>
      </div>

      <Footer />
      <CartSidebar isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </>
  );
}

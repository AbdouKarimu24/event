import { useEffect } from "react";
import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Calendar, Clock, MapPin, Ticket, User, Download, CheckCircle, XCircle } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/hooks/useAuth";
import Header from "@/components/header";
import Footer from "@/components/footer";
import CartSidebar from "@/components/cart-sidebar";
import { Link } from "wouter";
import type { BookingWithEvent } from "@shared/schema";
import { useState } from "react";

export default function Dashboard() {
  const { toast } = useToast();
  const { isAuthenticated, isLoading: authLoading, user } = useAuth();
  const [isCartOpen, setIsCartOpen] = useState(false);

  const { data: bookings = [], isLoading } = useQuery<BookingWithEvent[]>({
    queryKey: ["/api/bookings"],
    enabled: !!isAuthenticated,
  });

  // Redirect to login if not authenticated
  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      toast({
        title: "Unauthorized",
        description: "You are logged out. Logging in again...",
        variant: "destructive",
      });
      setTimeout(() => {
        window.location.href = "/api/login";
      }, 500);
      return;
    }
  }, [isAuthenticated, authLoading, toast]);

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-US", { 
      weekday: "long",
      year: "numeric",
      month: "long", 
      day: "numeric" 
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

  const formatBookingDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-US", { 
      month: "short", 
      day: "numeric",
      year: "numeric"
    });
  };

  const isEventUpcoming = (eventDate: string) => {
    const today = new Date();
    const event = new Date(eventDate);
    return event >= today;
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

  const upcomingBookings = bookings.filter(booking => 
    booking.status === "confirmed" && isEventUpcoming(booking.event.eventDate)
  );
  
  const pastBookings = bookings.filter(booking => 
    !isEventUpcoming(booking.event.eventDate) || booking.status === "cancelled"
  );

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
                {[...Array(4)].map((_, i) => (
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
          {/* Welcome Header */}
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-2">
              Welcome back, {user?.firstName || "User"}!
            </h1>
            <p className="text-gray-600">
              Manage your event bookings and discover new experiences.
            </p>
          </div>

          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <Ticket className="h-8 w-8 text-primary" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Total Bookings</p>
                    <p className="text-2xl font-bold text-gray-900">{bookings.length}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <CheckCircle className="h-8 w-8 text-green-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Upcoming Events</p>
                    <p className="text-2xl font-bold text-gray-900">{upcomingBookings.length}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <Calendar className="h-8 w-8 text-blue-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Past Events</p>
                    <p className="text-2xl font-bold text-gray-900">{pastBookings.length}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Bookings Section */}
          {bookings.length === 0 ? (
            <Card>
              <CardContent className="p-12 text-center">
                <Ticket className="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <h3 className="text-xl font-semibold text-gray-900 mb-2">No bookings yet</h3>
                <p className="text-gray-600 mb-6">
                  You haven't booked any events yet. Start exploring amazing events!
                </p>
                <Button asChild size="lg">
                  <Link href="/">Browse Events</Link>
                </Button>
              </CardContent>
            </Card>
          ) : (
            <div className="space-y-8">
              {/* Upcoming Bookings */}
              {upcomingBookings.length > 0 && (
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 mb-6">Upcoming Events</h2>
                  <div className="space-y-4">
                    {upcomingBookings.map((booking) => (
                      <Card key={booking.id} className="hover:shadow-lg transition-shadow">
                        <CardContent className="p-6">
                          <div className="flex items-start justify-between">
                            <div className="flex items-start space-x-4 flex-1">
                              <div className="w-20 h-20 bg-gradient-to-r from-primary/20 to-secondary/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                {booking.event.imageUrl ? (
                                  <img 
                                    src={booking.event.imageUrl}
                                    alt={booking.event.title}
                                    className="w-full h-full object-cover rounded-lg"
                                  />
                                ) : (
                                  <Calendar className="w-8 h-8 text-primary" />
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
                                
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
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
                                  <div className="flex items-center">
                                    <Ticket className="w-4 h-4 mr-2 text-primary" />
                                    <span>{booking.quantity} tickets</span>
                                  </div>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                  <div className="flex items-center space-x-4 text-sm">
                                    <div className="flex items-center">
                                      <User className="w-4 h-4 mr-1 text-gray-400" />
                                      <span>{booking.attendeeName}</span>
                                    </div>
                                    <div className="text-lg font-bold text-primary">
                                      ${parseFloat(booking.totalAmount).toFixed(2)}
                                    </div>
                                  </div>
                                  
                                  <div className="flex items-center space-x-2">
                                    <Button variant="outline" size="sm">
                                      <Download className="w-4 h-4 mr-2" />
                                      Download Ticket
                                    </Button>
                                    <Button variant="outline" size="sm" asChild>
                                      <Link href={`/events/${booking.event.id}`}>
                                        View Event
                                      </Link>
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
                </div>
              )}

              {/* Past Bookings */}
              {pastBookings.length > 0 && (
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 mb-6">Booking History</h2>
                  <div className="space-y-4">
                    {pastBookings.map((booking) => (
                      <Card key={booking.id} className="opacity-75">
                        <CardContent className="p-6">
                          <div className="flex items-start justify-between">
                            <div className="flex items-start space-x-4 flex-1">
                              <div className="w-16 h-16 bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg flex items-center justify-center flex-shrink-0">
                                {booking.event.imageUrl ? (
                                  <img 
                                    src={booking.event.imageUrl}
                                    alt={booking.event.title}
                                    className="w-full h-full object-cover rounded-lg grayscale"
                                  />
                                ) : (
                                  <Calendar className="w-6 h-6 text-gray-500" />
                                )}
                              </div>
                              
                              <div className="flex-1 min-w-0">
                                <div className="flex items-start justify-between mb-2">
                                  <div>
                                    <h3 className="text-lg font-semibold text-gray-700 mb-1">
                                      {booking.event.title}
                                    </h3>
                                    <p className="text-sm text-gray-500">
                                      Booked on {formatBookingDate(booking.createdAt)}
                                    </p>
                                  </div>
                                  <Badge className={`${getStatusColor(booking.status)} border`}>
                                    {booking.status}
                                  </Badge>
                                </div>
                                
                                <div className="flex items-center space-x-6 text-sm text-gray-500">
                                  <div className="flex items-center">
                                    <Calendar className="w-4 h-4 mr-2" />
                                    <span>{formatDate(booking.event.eventDate)}</span>
                                  </div>
                                  <div className="flex items-center">
                                    <Ticket className="w-4 h-4 mr-2" />
                                    <span>{booking.quantity} tickets</span>
                                  </div>
                                  <div className="font-medium">
                                    ${parseFloat(booking.totalAmount).toFixed(2)}
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      </div>

      <Footer />
      <CartSidebar isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </>
  );
}

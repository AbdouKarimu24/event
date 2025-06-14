import { useParams } from "wouter";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Calendar, Clock, MapPin, Users, DollarSign, ArrowLeft, ShoppingCart } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/hooks/useAuth";
import { apiRequest } from "@/lib/queryClient";
import { isUnauthorizedError } from "@/lib/authUtils";
import Header from "@/components/header";
import Footer from "@/components/footer";
import CartSidebar from "@/components/cart-sidebar";
import { Link } from "wouter";
import type { EventWithOrganizer } from "@shared/schema";
import { useState } from "react";

export default function EventDetails() {
  const { id } = useParams();
  const { toast } = useToast();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const queryClient = useQueryClient();
  const [isCartOpen, setIsCartOpen] = useState(false);

  const { data: event, isLoading, error } = useQuery<EventWithOrganizer>({
    queryKey: [`/api/events/${id}`],
    enabled: !!id,
  });

  const addToCartMutation = useMutation({
    mutationFn: async (quantity: number) => {
      await apiRequest("POST", "/api/cart", {
        eventId: parseInt(id!),
        quantity,
      });
    },
    onSuccess: () => {
      toast({
        title: "Added to Cart",
        description: "Event has been added to your cart successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/cart"] });
    },
    onError: (error) => {
      if (isUnauthorizedError(error)) {
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
      toast({
        title: "Error",
        description: "Failed to add event to cart. Please try again.",
        variant: "destructive",
      });
    },
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

  const getCategoryColor = (category: string) => {
    const colors = {
      music: "bg-purple-500",
      business: "bg-amber-500",
      technology: "bg-blue-500",
      arts: "bg-pink-500",
      sports: "bg-green-500",
      food: "bg-orange-500",
    };
    return colors[category.toLowerCase() as keyof typeof colors] || "bg-gray-500";
  };

  if (authLoading || isLoading) {
    return (
      <>
        <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
        <div className="min-h-screen bg-light">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div className="animate-pulse">
              <div className="h-8 bg-gray-200 rounded w-32 mb-6"></div>
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2">
                  <div className="h-96 bg-gray-200 rounded-lg mb-6"></div>
                  <div className="space-y-4">
                    <div className="h-8 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div className="space-y-2">
                      <div className="h-4 bg-gray-200 rounded"></div>
                      <div className="h-4 bg-gray-200 rounded w-5/6"></div>
                      <div className="h-4 bg-gray-200 rounded w-4/6"></div>
                    </div>
                  </div>
                </div>
                <div>
                  <div className="h-64 bg-gray-200 rounded-lg"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }

  if (error || !event) {
    return (
      <>
        <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
        <div className="min-h-screen bg-light flex items-center justify-center">
          <Card className="w-full max-w-md mx-4">
            <CardContent className="pt-6">
              <div className="text-center">
                <h1 className="text-2xl font-bold text-gray-900 mb-4">Event Not Found</h1>
                <p className="text-gray-600 mb-4">
                  The event you're looking for doesn't exist or has been removed.
                </p>
                <Button asChild>
                  <Link href="/">Back to Events</Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </>
    );
  }

  return (
    <>
      <Header onCartToggle={() => setIsCartOpen(!isCartOpen)} />
      
      <div className="min-h-screen bg-light">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* Back Button */}
          <Button variant="outline" className="mb-6" asChild>
            <Link href="/">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back to Events
            </Link>
          </Button>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2">
              {/* Event Image */}
              <div className="relative mb-6">
                <div className="h-96 rounded-lg overflow-hidden bg-gradient-to-r from-primary/20 to-secondary/20 flex items-center justify-center">
                  {event.imageUrl ? (
                    <img 
                      src={event.imageUrl}
                      alt={event.title}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="text-center text-gray-500">
                      <Calendar className="w-24 h-24 mx-auto mb-4" />
                      <p className="text-lg">No Image Available</p>
                    </div>
                  )}
                </div>
                <Badge 
                  className={`absolute top-4 left-4 ${getCategoryColor(event.category)} text-white px-3 py-1 text-sm`}
                >
                  {event.category}
                </Badge>
              </div>

              {/* Event Details */}
              <div className="space-y-6">
                <div>
                  <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    {event.title}
                  </h1>
                  {event.organizer && (
                    <p className="text-gray-600">
                      Organized by {event.organizer.firstName} {event.organizer.lastName}
                    </p>
                  )}
                </div>

                {/* Event Info */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="flex items-center space-x-3">
                    <div className="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                      <Calendar className="w-5 h-5 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-900">Date</p>
                      <p className="text-gray-600">{formatDate(event.eventDate)}</p>
                    </div>
                  </div>

                  {event.startTime && (
                    <div className="flex items-center space-x-3">
                      <div className="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <Clock className="w-5 h-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium text-gray-900">Time</p>
                        <p className="text-gray-600">
                          {formatTime(event.startTime)}
                          {event.endTime && ` - ${formatTime(event.endTime)}`}
                        </p>
                      </div>
                    </div>
                  )}

                  <div className="flex items-center space-x-3">
                    <div className="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                      <MapPin className="w-5 h-5 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-900">Location</p>
                      <p className="text-gray-600">
                        {event.venue}
                        {event.address && <><br />{event.address}</>}
                        {event.city && event.state && <><br />{event.city}, {event.state}</>}
                      </p>
                    </div>
                  </div>

                  {event.availableTickets && (
                    <div className="flex items-center space-x-3">
                      <div className="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <Users className="w-5 h-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium text-gray-900">Available Tickets</p>
                        <p className="text-gray-600">{event.availableTickets} tickets remaining</p>
                      </div>
                    </div>
                  )}
                </div>

                {/* Description */}
                {event.description && (
                  <div>
                    <h2 className="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                    <div className="prose prose-gray max-w-none">
                      <p className="text-gray-600 leading-relaxed whitespace-pre-wrap">
                        {event.description}
                      </p>
                    </div>
                  </div>
                )}

                {/* Location Map Placeholder */}
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 mb-4">Location</h2>
                  <div className="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                    <div className="text-center text-gray-500">
                      <MapPin className="w-12 h-12 mx-auto mb-2" />
                      <p>Map integration coming soon</p>
                      <p className="text-sm">{event.venue}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Booking Sidebar */}
            <div className="lg:col-span-1">
              <Card className="sticky top-24">
                <CardContent className="p-6">
                  <div className="text-center mb-6">
                    <div className="flex items-center justify-center space-x-2 mb-2">
                      <DollarSign className="w-6 h-6 text-primary" />
                      <span className="text-3xl font-bold text-primary">
                        {parseFloat(event.price).toFixed(2)}
                      </span>
                    </div>
                    <p className="text-gray-600">per ticket</p>
                  </div>

                  <div className="space-y-4">
                    <Button
                      className="w-full bg-primary hover:bg-primary/90"
                      onClick={() => addToCartMutation.mutate(1)}
                      disabled={addToCartMutation.isPending || event.availableTickets === 0}
                    >
                      {addToCartMutation.isPending ? (
                        "Adding to Cart..."
                      ) : event.availableTickets === 0 ? (
                        "Sold Out"
                      ) : (
                        <>
                          <ShoppingCart className="w-4 h-4 mr-2" />
                          Add to Cart
                        </>
                      )}
                    </Button>

                    <Button
                      variant="outline"
                      className="w-full"
                      asChild
                    >
                      <Link href="/cart">
                        View Cart
                      </Link>
                    </Button>
                  </div>

                  {event.availableTickets && event.availableTickets < 10 && event.availableTickets > 0 && (
                    <div className="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                      <p className="text-sm text-orange-800 font-medium">
                        Hurry! Only {event.availableTickets} tickets left
                      </p>
                    </div>
                  )}

                  <div className="mt-6 pt-6 border-t">
                    <h3 className="font-semibold text-gray-900 mb-3">Event Highlights</h3>
                    <ul className="space-y-2 text-sm text-gray-600">
                      <li>• Instant booking confirmation</li>
                      <li>• Mobile-friendly tickets</li>
                      <li>• Secure payment processing</li>
                      <li>• 24/7 customer support</li>
                    </ul>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>

      <Footer />
      <CartSidebar isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </>
  );
}

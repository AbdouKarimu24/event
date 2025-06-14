import { Link } from "wouter";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Calendar, Clock, MapPin, Heart } from "lucide-react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { isUnauthorizedError } from "@/lib/authUtils";
import type { EventWithOrganizer } from "@shared/schema";

interface EventCardProps {
  event: EventWithOrganizer;
  viewMode?: "grid" | "list";
}

export default function EventCard({ event, viewMode = "grid" }: EventCardProps) {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const addToCartMutation = useMutation({
    mutationFn: async () => {
      await apiRequest("POST", "/api/cart", {
        eventId: event.id,
        quantity: 1,
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

  const isGridView = viewMode === "grid";

  return (
    <Card className={`bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden ${
      isGridView ? "" : "flex"
    }`}>
      {/* Event Image */}
      <div className={`${isGridView ? "w-full h-48" : "w-64 h-40"} bg-gradient-to-r from-primary/20 to-secondary/20 flex items-center justify-center relative`}>
        {event.imageUrl ? (
          <img 
            src={event.imageUrl}
            alt={event.title}
            className="w-full h-full object-cover"
          />
        ) : (
          <div className="text-center text-gray-500">
            <Calendar className="w-12 h-12 mx-auto mb-2" />
            <p className="text-sm">No Image</p>
          </div>
        )}
        <Button
          variant="ghost"
          size="sm"
          className="absolute top-2 right-2 text-gray-400 hover:text-red-500 bg-white/80 hover:bg-white"
        >
          <Heart className="w-4 h-4" />
        </Button>
      </div>

      {/* Event Content */}
      <CardContent className={`p-4 ${isGridView ? "" : "flex-1"}`}>
        <div className="flex items-center justify-between mb-2">
          <Badge className={`${getCategoryColor(event.category)} text-white text-xs px-2 py-1`}>
            {event.category}
          </Badge>
        </div>
        
        <h4 className="font-semibold text-lg text-gray-900 mb-2 line-clamp-2">
          {event.title}
        </h4>
        
        <div className="space-y-1 text-sm text-gray-600 mb-3">
          <div className="flex items-center">
            <Calendar className="w-4 h-4 text-primary mr-2 flex-shrink-0" />
            <span>{formatDate(event.eventDate)}</span>
          </div>
          {event.startTime && (
            <div className="flex items-center">
              <Clock className="w-4 h-4 text-primary mr-2 flex-shrink-0" />
              <span>
                {formatTime(event.startTime)}
                {event.endTime && ` - ${formatTime(event.endTime)}`}
              </span>
            </div>
          )}
          <div className="flex items-center">
            <MapPin className="w-4 h-4 text-primary mr-2 flex-shrink-0" />
            <span className="truncate">
              {event.venue}
              {event.city && `, ${event.city}`}
            </span>
          </div>
        </div>
        
        {!isGridView && event.description && (
          <p className="text-sm text-gray-600 mb-3 line-clamp-2">
            {event.description}
          </p>
        )}
        
        <div className={`flex items-center justify-between ${isGridView ? "" : "mt-4"}`}>
          <div className="text-lg font-bold text-primary">
            ${parseFloat(event.price).toFixed(2)}
          </div>
          <div className="flex space-x-2">
            {!isGridView && (
              <Button
                size="sm"
                variant="outline"
                onClick={() => addToCartMutation.mutate()}
                disabled={addToCartMutation.isPending}
                className="text-primary border-primary hover:bg-primary hover:text-white"
              >
                Add to Cart
              </Button>
            )}
            <Button size="sm" asChild>
              <Link href={`/events/${event.id}`}>
                View Details
              </Link>
            </Button>
          </div>
        </div>
        
        {isGridView && (
          <Button
            size="sm"
            variant="outline"
            onClick={() => addToCartMutation.mutate()}
            disabled={addToCartMutation.isPending}
            className="w-full mt-2 text-primary border-primary hover:bg-primary hover:text-white"
          >
            Add to Cart
          </Button>
        )}
      </CardContent>
    </Card>
  );
}

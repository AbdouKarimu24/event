import { useState, useEffect } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { ShoppingCart, CreditCard, User, Mail, Phone, MapPin, Calendar, Clock, Trash2, ArrowLeft } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/hooks/useAuth";
import { apiRequest } from "@/lib/queryClient";
import { isUnauthorizedError } from "@/lib/authUtils";
import Header from "@/components/header";
import Footer from "@/components/footer";
import { Link } from "wouter";
import type { CartItemWithEvent } from "@shared/schema";

const checkoutSchema = z.object({
  attendeeName: z.string().min(2, "Name must be at least 2 characters"),
  attendeeEmail: z.string().email("Please enter a valid email address"),
  attendeePhone: z.string().min(10, "Please enter a valid phone number").optional(),
});

type CheckoutFormData = z.infer<typeof checkoutSchema>;

export default function Cart() {
  const { toast } = useToast();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const queryClient = useQueryClient();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const form = useForm<CheckoutFormData>({
    resolver: zodResolver(checkoutSchema),
    defaultValues: {
      attendeeName: "",
      attendeeEmail: "",
      attendeePhone: "",
    },
  });

  const { data: cartItems = [], isLoading } = useQuery<CartItemWithEvent[]>({
    queryKey: ["/api/cart"],
    enabled: !!isAuthenticated,
  });

  const updateCartMutation = useMutation({
    mutationFn: async ({ id, quantity }: { id: number; quantity: number }) => {
      await apiRequest("PUT", `/api/cart/${id}`, { quantity });
    },
    onSuccess: () => {
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
        description: "Failed to update cart item.",
        variant: "destructive",
      });
    },
  });

  const removeFromCartMutation = useMutation({
    mutationFn: async (id: number) => {
      await apiRequest("DELETE", `/api/cart/${id}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/cart"] });
      toast({
        title: "Removed from Cart",
        description: "Item has been removed from your cart.",
      });
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
        description: "Failed to remove item from cart.",
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

  const total = cartItems.reduce((sum, item) => 
    sum + (parseFloat(item.event.price) * item.quantity), 0
  );

  const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);

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

  const onSubmit = async (data: CheckoutFormData) => {
    if (cartItems.length === 0) {
      toast({
        title: "Empty Cart",
        description: "Please add items to your cart before checkout.",
        variant: "destructive",
      });
      return;
    }

    setIsSubmitting(true);
    try {
      // Create bookings for each cart item
      for (const item of cartItems) {
        await apiRequest("POST", "/api/bookings", {
          eventId: item.eventId,
          quantity: item.quantity,
          totalAmount: parseFloat(item.event.price) * item.quantity,
          attendeeName: data.attendeeName,
          attendeeEmail: data.attendeeEmail,
          attendeePhone: data.attendeePhone || "",
        });
      }

      toast({
        title: "Booking Confirmed!",
        description: "Your tickets have been booked successfully. Check your dashboard for details.",
      });

      // Redirect to dashboard
      window.location.href = "/dashboard";
    } catch (error) {
      if (isUnauthorizedError(error as Error)) {
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
        title: "Booking Failed",
        description: "There was an error processing your booking. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (authLoading || isLoading) {
    return (
      <>
        <Header onCartToggle={() => {}} />
        <div className="min-h-screen bg-light">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div className="animate-pulse">
              <div className="h-8 bg-gray-200 rounded w-32 mb-6"></div>
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-4">
                  {[...Array(3)].map((_, i) => (
                    <div key={i} className="h-32 bg-gray-200 rounded-lg"></div>
                  ))}
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

  return (
    <>
      <Header onCartToggle={() => {}} />
      
      <div className="min-h-screen bg-light">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* Header */}
          <div className="flex items-center justify-between mb-8">
            <div className="flex items-center space-x-4">
              <Button variant="outline" asChild>
                <Link href="/">
                  <ArrowLeft className="w-4 h-4 mr-2" />
                  Continue Shopping
                </Link>
              </Button>
              <div>
                <h1 className="text-3xl font-bold text-gray-900 flex items-center">
                  <ShoppingCart className="w-8 h-8 mr-3" />
                  Shopping Cart
                </h1>
                <p className="text-gray-600">
                  {totalItems} {totalItems === 1 ? "item" : "items"} in your cart
                </p>
              </div>
            </div>
          </div>

          {cartItems.length === 0 ? (
            <div className="text-center py-16">
              <ShoppingCart className="w-24 h-24 mx-auto text-gray-300 mb-6" />
              <h2 className="text-2xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
              <p className="text-gray-600 mb-6">
                Looks like you haven't added any events to your cart yet.
              </p>
              <Button asChild size="lg">
                <Link href="/">Browse Events</Link>
              </Button>
            </div>
          ) : (
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Cart Items */}
              <div className="lg:col-span-2 space-y-4">
                {cartItems.map((item) => (
                  <Card key={item.id}>
                    <CardContent className="p-6">
                      <div className="flex items-start space-x-4">
                        <div className="w-24 h-24 bg-gradient-to-r from-primary/20 to-secondary/20 rounded-lg flex items-center justify-center flex-shrink-0">
                          {item.event.imageUrl ? (
                            <img 
                              src={item.event.imageUrl}
                              alt={item.event.title}
                              className="w-full h-full object-cover rounded-lg"
                            />
                          ) : (
                            <Calendar className="w-8 h-8 text-primary" />
                          )}
                        </div>
                        
                        <div className="flex-1 min-w-0">
                          <h3 className="text-lg font-semibold text-gray-900 mb-2">
                            {item.event.title}
                          </h3>
                          
                          <div className="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                            <div className="flex items-center">
                              <Calendar className="w-4 h-4 mr-2" />
                              <span>{formatDate(item.event.eventDate)}</span>
                            </div>
                            {item.event.startTime && (
                              <div className="flex items-center">
                                <Clock className="w-4 h-4 mr-2" />
                                <span>{formatTime(item.event.startTime)}</span>
                              </div>
                            )}
                            <div className="flex items-center">
                              <MapPin className="w-4 h-4 mr-2" />
                              <span>{item.event.venue}</span>
                            </div>
                            <div className="flex items-center">
                              <CreditCard className="w-4 h-4 mr-2" />
                              <span>${parseFloat(item.event.price).toFixed(2)} each</span>
                            </div>
                          </div>
                          
                          <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-4">
                              <Label htmlFor={`quantity-${item.id}`} className="text-sm font-medium">
                                Quantity:
                              </Label>
                              <Input
                                id={`quantity-${item.id}`}
                                type="number"
                                min="1"
                                value={item.quantity}
                                onChange={(e) => {
                                  const quantity = parseInt(e.target.value);
                                  if (quantity > 0) {
                                    updateCartMutation.mutate({ id: item.id, quantity });
                                  }
                                }}
                                className="w-20 h-8"
                              />
                            </div>
                            
                            <div className="flex items-center space-x-4">
                              <div className="text-lg font-bold text-primary">
                                ${(parseFloat(item.event.price) * item.quantity).toFixed(2)}
                              </div>
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => removeFromCartMutation.mutate(item.id)}
                                disabled={removeFromCartMutation.isPending}
                                className="text-red-600 hover:text-red-700 hover:bg-red-50"
                              >
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>

              {/* Checkout Form */}
              <div className="lg:col-span-1">
                <Card className="sticky top-24">
                  <CardHeader>
                    <CardTitle className="flex items-center">
                      <CreditCard className="w-5 h-5 mr-2" />
                      Checkout
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    {/* Order Summary */}
                    <div>
                      <h3 className="font-semibold mb-3">Order Summary</h3>
                      <div className="space-y-2 text-sm">
                        <div className="flex justify-between">
                          <span>Subtotal ({totalItems} items)</span>
                          <span>${total.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between">
                          <span>Service Fee</span>
                          <span>$0.00</span>
                        </div>
                        <Separator />
                        <div className="flex justify-between font-semibold text-lg">
                          <span>Total</span>
                          <span className="text-primary">${total.toFixed(2)}</span>
                        </div>
                      </div>
                    </div>

                    {/* Attendee Information Form */}
                    <Form {...form}>
                      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                        <div>
                          <h3 className="font-semibold mb-3 flex items-center">
                            <User className="w-4 h-4 mr-2" />
                            Attendee Information
                          </h3>
                          
                          <FormField
                            control={form.control}
                            name="attendeeName"
                            render={({ field }) => (
                              <FormItem>
                                <FormLabel>Full Name</FormLabel>
                                <FormControl>
                                  <Input placeholder="Enter full name" {...field} />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            )}
                          />
                          
                          <FormField
                            control={form.control}
                            name="attendeeEmail"
                            render={({ field }) => (
                              <FormItem>
                                <FormLabel>Email Address</FormLabel>
                                <FormControl>
                                  <Input type="email" placeholder="Enter email address" {...field} />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            )}
                          />
                          
                          <FormField
                            control={form.control}
                            name="attendeePhone"
                            render={({ field }) => (
                              <FormItem>
                                <FormLabel>Phone Number (Optional)</FormLabel>
                                <FormControl>
                                  <Input type="tel" placeholder="Enter phone number" {...field} />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            )}
                          />
                        </div>

                        <div className="pt-4 border-t">
                          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 className="font-medium text-blue-900 mb-2">Payment Information</h4>
                            <p className="text-sm text-blue-800">
                              This is a demo booking system. No actual payment will be processed.
                              Your booking will be confirmed immediately.
                            </p>
                          </div>
                          
                          <Button
                            type="submit"
                            className="w-full bg-primary hover:bg-primary/90 text-white"
                            disabled={isSubmitting || cartItems.length === 0}
                          >
                            {isSubmitting ? "Processing..." : `Complete Booking - $${total.toFixed(2)}`}
                          </Button>
                        </div>
                      </form>
                    </Form>
                  </CardContent>
                </Card>
              </div>
            </div>
          )}
        </div>
      </div>

      <Footer />
    </>
  );
}

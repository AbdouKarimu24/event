import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { X, Trash2, ShoppingBag } from "lucide-react";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { isUnauthorizedError } from "@/lib/authUtils";
import { Link } from "wouter";
import type { CartItemWithEvent } from "@shared/schema";

interface CartSidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function CartSidebar({ isOpen, onClose }: CartSidebarProps) {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: cartItems = [], isLoading } = useQuery<CartItemWithEvent[]>({
    queryKey: ["/api/cart"],
    enabled: isOpen,
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

  const total = cartItems.reduce((sum, item) => 
    sum + (parseFloat(item.event.price) * item.quantity), 0
  );

  const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-US", { 
      month: "short", 
      day: "numeric" 
    });
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-hidden">
      <div className="absolute inset-0 bg-black bg-opacity-50" onClick={onClose}></div>
      <div className={`absolute right-0 top-0 h-full w-96 bg-white shadow-lg transform transition-transform duration-300 ${
        isOpen ? "translate-x-0" : "translate-x-full"
      }`}>
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b">
          <h3 className="text-lg font-semibold flex items-center">
            <ShoppingBag className="w-5 h-5 mr-2" />
            Your Cart ({totalItems})
          </h3>
          <Button variant="ghost" size="sm" onClick={onClose}>
            <X className="w-4 h-4" />
          </Button>
        </div>

        {/* Cart Items */}
        <div className="flex-1 overflow-y-auto p-4">
          {isLoading ? (
            <div className="space-y-4">
              {[...Array(3)].map((_, i) => (
                <div key={i} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg animate-pulse">
                  <div className="w-12 h-12 bg-gray-200 rounded"></div>
                  <div className="flex-1 space-y-2">
                    <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                  </div>
                </div>
              ))}
            </div>
          ) : cartItems.length === 0 ? (
            <div className="text-center py-16">
              <ShoppingBag className="w-16 h-16 mx-auto text-gray-300 mb-4" />
              <h4 className="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h4>
              <p className="text-gray-600 mb-4">Start adding some events to your cart!</p>
              <Button onClick={onClose}>
                Continue Browsing
              </Button>
            </div>
          ) : (
            <div className="space-y-4">
              {cartItems.map((item) => (
                <div key={item.id} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <div className="w-12 h-12 bg-gradient-to-r from-primary/20 to-secondary/20 rounded flex items-center justify-center">
                    {item.event.imageUrl ? (
                      <img 
                        src={item.event.imageUrl}
                        alt={item.event.title}
                        className="w-full h-full object-cover rounded"
                      />
                    ) : (
                      <ShoppingBag className="w-6 h-6 text-primary" />
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <h4 className="font-medium text-sm truncate">{item.event.title}</h4>
                    <p className="text-xs text-gray-600">
                      {formatDate(item.event.eventDate)} • ${parseFloat(item.event.price).toFixed(2)}
                    </p>
                    <div className="flex items-center space-x-2 mt-1">
                      <Input
                        type="number"
                        min="1"
                        value={item.quantity}
                        onChange={(e) => {
                          const quantity = parseInt(e.target.value);
                          if (quantity > 0) {
                            updateCartMutation.mutate({ id: item.id, quantity });
                          }
                        }}
                        className="w-16 h-6 text-xs"
                      />
                      <span className="text-xs text-gray-500">
                        × ${parseFloat(item.event.price).toFixed(2)}
                      </span>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm font-medium">
                      ${(parseFloat(item.event.price) * item.quantity).toFixed(2)}
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => removeFromCartMutation.mutate(item.id)}
                      disabled={removeFromCartMutation.isPending}
                      className="text-red-500 hover:text-red-600 p-1 h-auto"
                    >
                      <Trash2 className="w-3 h-3" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Footer */}
        {cartItems.length > 0 && (
          <div className="p-4 border-t bg-gray-50">
            <div className="flex justify-between items-center mb-4">
              <span className="font-semibold">Total:</span>
              <span className="font-bold text-lg text-primary">${total.toFixed(2)}</span>
            </div>
            <Button 
              className="w-full bg-primary text-white hover:bg-primary/90"
              asChild
              onClick={onClose}
            >
              <Link href="/cart">
                Proceed to Checkout
              </Link>
            </Button>
          </div>
        )}
      </div>
    </div>
  );
}

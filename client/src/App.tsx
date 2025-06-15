import { Switch, Route } from "wouter";
import { queryClient } from "./lib/queryClient";
import { QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { useAuth } from "@/hooks/useAuth";
import NotFound from "@/pages/not-found";
import Landing from "@/pages/landing";
import Home from "@/pages/home";
import EventDetails from "@/pages/event-details";
import Cart from "@/pages/cart";
import Dashboard from "@/pages/dashboard";
import AdminEvents from "@/pages/admin/events";
import AdminBookings from "@/pages/admin/bookings";
import AdminAnalytics from "@/pages/admin/analytics";
import DatabaseAdmin from "@/pages/admin/database";

function Router() {
  const { isAuthenticated, isLoading } = useAuth();

  return (
    <Switch>
      {isLoading || !isAuthenticated ? (
        <Route path="/" component={Landing} />
      ) : (
        <>
          <Route path="/" component={Home} />
          <Route path="/events/:id" component={EventDetails} />
          <Route path="/cart" component={Cart} />
          <Route path="/dashboard" component={Dashboard} />
          <Route path="/admin/events" component={AdminEvents} />
          <Route path="/admin/bookings" component={AdminBookings} />
          <Route path="/admin/analytics" component={AdminAnalytics} />
          <Route path="/admin/database" component={DatabaseAdmin} />
        </>
      )}
      <Route component={NotFound} />
    </Switch>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <Toaster />
        <Router />
      </TooltipProvider>
    </QueryClientProvider>
  );
}

export default App;

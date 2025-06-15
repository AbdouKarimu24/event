import { useState } from "react";
import { useQuery, useMutation } from "@tanstack/react-query";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { AlertTriangle, Database, Play, RefreshCw } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { apiRequest, queryClient } from "@/lib/queryClient";

interface TableInfo {
  table_name: string;
  row_count: number;
  table_size: string;
}

interface QueryResult {
  columns: string[];
  rows: any[][];
  rowCount: number;
  executionTime: number;
}

export default function DatabaseAdmin() {
  const { toast } = useToast();
  const [sqlQuery, setSqlQuery] = useState("");
  const [selectedTable, setSelectedTable] = useState<string>("");

  // Fetch table information
  const { data: tables, isLoading: tablesLoading } = useQuery({
    queryKey: ["/api/admin/database/tables"],
    retry: false,
  });

  // Execute SQL query mutation
  const executeQueryMutation = useMutation({
    mutationFn: async (query: string) => {
      return await apiRequest("/api/admin/database/execute", "POST", { query });
    },
    onSuccess: (data) => {
      toast({
        title: "Query executed successfully",
        description: `${data.rowCount} rows affected in ${data.executionTime}ms`,
      });
    },
    onError: (error) => {
      toast({
        title: "Query execution failed",
        description: error.message,
        variant: "destructive",
      });
    },
  });

  // Fetch table data
  const { data: tableData } = useQuery({
    queryKey: ["/api/admin/database/table", selectedTable],
    enabled: !!selectedTable,
    retry: false,
  });

  const handleExecuteQuery = () => {
    if (!sqlQuery.trim()) {
      toast({
        title: "Empty query",
        description: "Please enter a SQL query to execute",
        variant: "destructive",
      });
      return;
    }
    executeQueryMutation.mutate(sqlQuery);
  };

  const loadSampleQueries = (type: string) => {
    const queries = {
      users: "SELECT id, email, firstName, lastName, role, createdAt FROM users ORDER BY createdAt DESC LIMIT 10;",
      events: "SELECT id, title, category, venue, eventDate, price, status FROM events WHERE status = 'active' ORDER BY eventDate DESC LIMIT 10;",
      bookings: "SELECT b.id, b.attendeeName, b.totalAmount, b.status, e.title as event_title FROM bookings b JOIN events e ON b.eventId = e.id ORDER BY b.createdAt DESC LIMIT 10;",
      analytics: "SELECT category, COUNT(*) as event_count, AVG(price) as avg_price FROM events GROUP BY category ORDER BY event_count DESC;"
    };
    setSqlQuery(queries[type as keyof typeof queries] || "");
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Database Administration</h1>
        <p className="text-muted-foreground">
          Manage your database with SQL queries and table browsing
        </p>
      </div>

      <Tabs defaultValue="query" className="space-y-6">
        <TabsList>
          <TabsTrigger value="query">SQL Query</TabsTrigger>
          <TabsTrigger value="tables">Browse Tables</TabsTrigger>
          <TabsTrigger value="info">Database Info</TabsTrigger>
        </TabsList>

        <TabsContent value="query" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Play className="h-5 w-5" />
                SQL Query Executor
              </CardTitle>
              <CardDescription>
                Execute SQL queries directly on your database. Use with caution in production.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-2 flex-wrap">
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={() => loadSampleQueries("users")}
                >
                  Sample: Users
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={() => loadSampleQueries("events")}
                >
                  Sample: Events
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={() => loadSampleQueries("bookings")}
                >
                  Sample: Bookings
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={() => loadSampleQueries("analytics")}
                >
                  Sample: Analytics
                </Button>
              </div>

              <Textarea
                placeholder="Enter your SQL query here..."
                value={sqlQuery}
                onChange={(e) => setSqlQuery(e.target.value)}
                className="min-h-[150px] font-mono"
              />

              <div className="flex gap-2">
                <Button 
                  onClick={handleExecuteQuery}
                  disabled={executeQueryMutation.isPending}
                  className="flex items-center gap-2"
                >
                  {executeQueryMutation.isPending ? (
                    <RefreshCw className="h-4 w-4 animate-spin" />
                  ) : (
                    <Play className="h-4 w-4" />
                  )}
                  Execute Query
                </Button>
                <Button 
                  variant="outline" 
                  onClick={() => setSqlQuery("")}
                >
                  Clear
                </Button>
              </div>

              {executeQueryMutation.data && (
                <Card>
                  <CardHeader>
                    <CardTitle>Query Results</CardTitle>
                    <CardDescription>
                      {executeQueryMutation.data.rowCount} rows • {executeQueryMutation.data.executionTime}ms
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    {executeQueryMutation.data.rows.length > 0 ? (
                      <div className="overflow-auto max-h-96">
                        <Table>
                          <TableHeader>
                            <TableRow>
                              {executeQueryMutation.data.columns.map((column: string) => (
                                <TableHead key={column}>{column}</TableHead>
                              ))}
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {executeQueryMutation.data.rows.map((row: any[], index: number) => (
                              <TableRow key={index}>
                                {row.map((cell, cellIndex) => (
                                  <TableCell key={cellIndex}>
                                    {cell === null ? (
                                      <span className="text-muted-foreground italic">NULL</span>
                                    ) : (
                                      String(cell)
                                    )}
                                  </TableCell>
                                ))}
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </div>
                    ) : (
                      <p className="text-muted-foreground">No results returned</p>
                    )}
                  </CardContent>
                </Card>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="tables" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Database className="h-5 w-5" />
                Database Tables
              </CardTitle>
              <CardDescription>
                Browse and explore your database tables
              </CardDescription>
            </CardHeader>
            <CardContent>
              {tablesLoading ? (
                <div className="flex items-center gap-2">
                  <RefreshCw className="h-4 w-4 animate-spin" />
                  Loading tables...
                </div>
              ) : tables && tables.length > 0 ? (
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  {tables.map((table: TableInfo) => (
                    <Card 
                      key={table.table_name}
                      className={`cursor-pointer transition-colors ${
                        selectedTable === table.table_name ? 'ring-2 ring-primary' : ''
                      }`}
                      onClick={() => setSelectedTable(table.table_name)}
                    >
                      <CardContent className="p-4">
                        <h3 className="font-semibold">{table.table_name}</h3>
                        <div className="mt-2 space-y-1 text-sm text-muted-foreground">
                          <div>Rows: {table.row_count}</div>
                          <div>Size: {table.table_size}</div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              ) : (
                <p className="text-muted-foreground">No tables found</p>
              )}

              {selectedTable && tableData && (
                <Card className="mt-6">
                  <CardHeader>
                    <CardTitle>Table: {selectedTable}</CardTitle>
                    <CardDescription>
                      First 50 rows from {selectedTable}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="overflow-auto max-h-96">
                      <Table>
                        <TableHeader>
                          <TableRow>
                            {tableData.columns.map((column: string) => (
                              <TableHead key={column}>{column}</TableHead>
                            ))}
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {tableData.rows.map((row: any[], index: number) => (
                            <TableRow key={index}>
                              {row.map((cell, cellIndex) => (
                                <TableCell key={cellIndex}>
                                  {cell === null ? (
                                    <span className="text-muted-foreground italic">NULL</span>
                                  ) : (
                                    String(cell)
                                  )}
                                </TableCell>
                              ))}
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </div>
                  </CardContent>
                </Card>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="info" className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Database Overview</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center gap-2">
                  <Badge variant="secondary">PostgreSQL</Badge>
                  <span className="text-sm text-muted-foreground">Modern, secure database</span>
                </div>
                <div className="space-y-2 text-sm">
                  <div>
                    <strong>Connection:</strong> Secure encrypted connection
                  </div>
                  <div>
                    <strong>Features:</strong> ACID compliance, advanced indexing
                  </div>
                  <div>
                    <strong>Backup:</strong> Automated daily backups
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <AlertTriangle className="h-5 w-5 text-amber-500" />
                  Safety Guidelines
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2 text-sm">
                <div>• Always backup before running DELETE or UPDATE queries</div>
                <div>• Use SELECT queries to verify data before modifications</div>
                <div>• Avoid dropping tables or columns in production</div>
                <div>• Use transactions for multiple related operations</div>
                <div>• Test queries on a small dataset first</div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
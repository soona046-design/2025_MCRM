import { MessageSquare } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

export function RecentInquiries() {
  return (
    <Card className="border border-border/50 shadow-md">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg font-semibold">최근 문의</CardTitle>
          <MessageSquare className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col items-center justify-center py-12">
          <div className="rounded-full bg-muted/70 p-4 mb-4">
            <MessageSquare className="h-8 w-8 text-muted-foreground" />
          </div>
          <p className="text-sm text-muted-foreground text-center font-medium">등록된 문의가 없습니다</p>
          <p className="text-xs text-muted-foreground/70 mt-1">새로운 문의가 들어오면 여기에 표시됩니다</p>
        </div>
      </CardContent>
    </Card>
  )
}

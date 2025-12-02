import { PieChart } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

export function InquiryDistribution() {
  return (
    <Card className="border border-border/50 shadow-md">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg font-semibold">채널별 문의 분포</CardTitle>
          <PieChart className="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col items-center justify-center py-12">
          <div className="rounded-full bg-muted/70 p-4 mb-4">
            <PieChart className="h-8 w-8 text-muted-foreground" />
          </div>
          <p className="text-sm text-muted-foreground text-center font-medium">데이터가 없습니다</p>
          <p className="text-xs text-muted-foreground/70 mt-1">문의 데이터가 수집되면 분포 차트가 표시됩니다</p>
        </div>
      </CardContent>
    </Card>
  )
}

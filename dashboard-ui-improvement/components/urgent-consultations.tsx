import { AlertCircle, Clock } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"

const urgentItems = [
  {
    id: 1,
    title: "SLA 임박 - 교정 문의",
    assignee: "리드: 이철진 | 담당자: 이상담",
    tags: ["상태: 진행", "30분 남음"],
    status: "긴급",
    timeLeft: "30분",
  },
]

export function UrgentConsultations() {
  return (
    <Card className="border border-border/50 shadow-md">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg font-semibold">긴급 상담</CardTitle>
          <Badge variant="destructive" className="gap-1 shadow-sm">
            <AlertCircle className="h-3 w-3" />
            1건
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        {urgentItems.map((item) => (
          <div
            key={item.id}
            className="rounded-lg border-2 border-destructive/30 bg-destructive/5 p-4 hover:bg-destructive/10 hover:border-destructive/40 transition-all duration-200 cursor-pointer shadow-sm"
          >
            <div className="flex items-start justify-between mb-3">
              <div className="space-y-1 flex-1">
                <div className="flex items-center gap-2">
                  <h4 className="font-semibold text-sm">{item.title}</h4>
                  <Badge variant="destructive" className="text-xs">
                    {item.status}
                  </Badge>
                </div>
                <p className="text-xs text-muted-foreground">{item.assignee}</p>
              </div>

              <div className="flex items-center gap-1.5 text-destructive">
                <Clock className="h-4 w-4" />
                <span className="text-sm font-semibold">{item.timeLeft}</span>
              </div>
            </div>

            <div className="flex flex-wrap gap-2">
              {item.tags.map((tag) => (
                <Badge key={tag} variant="outline" className="text-xs">
                  {tag}
                </Badge>
              ))}
            </div>
          </div>
        ))}
      </CardContent>
    </Card>
  )
}

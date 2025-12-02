import { MessageSquare, Users, AlertCircle, UserCheck } from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"

const stats = [
  {
    title: "전체 문의",
    value: "0",
    subtitle: "등록된 문의",
    icon: MessageSquare,
    color: "text-chart-1",
    bgColor: "bg-chart-1/10",
  },
  {
    title: "진행중 상담",
    value: "3",
    subtitle: "진행중인 상담",
    icon: Users,
    color: "text-chart-2",
    bgColor: "bg-chart-2/10",
  },
  {
    title: "긴급 상담",
    value: "1",
    subtitle: "긴급/SLA 임박",
    icon: AlertCircle,
    color: "text-destructive",
    bgColor: "bg-destructive/10",
  },
  {
    title: "전체 사용자",
    value: "4",
    subtitle: "등록된 사용자",
    icon: UserCheck,
    color: "text-chart-3",
    bgColor: "bg-chart-3/10",
  },
]

export function StatsCards() {
  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      {stats.map((stat) => {
        const Icon = stat.icon
        return (
          <Card
            key={stat.title}
            className="border border-border/50 shadow-md hover:shadow-lg transition-all duration-200"
          >
            <CardContent className="p-6">
              <div className="flex items-start justify-between">
                <div className="space-y-2">
                  <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                  <div className="flex items-baseline gap-2">
                    <h3 className={`text-4xl font-bold ${stat.color}`}>{stat.value}</h3>
                  </div>
                  <p className="text-xs text-muted-foreground">{stat.subtitle}</p>
                </div>
                <div className={`${stat.bgColor} ${stat.color} p-3 rounded-xl`}>
                  <Icon className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>
        )
      })}
    </div>
  )
}

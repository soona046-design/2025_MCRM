import { Users } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

const users = [
  {
    id: 1,
    name: "김상담",
    email: "kim.agent@clinic.com",
    status: "상담배니저",
    active: true,
  },
  {
    id: 2,
    name: "이상담",
    email: "lee.agent@clinic.com",
    status: "상담배니저",
    active: true,
  },
]

export function UserStatus() {
  return (
    <Card className="border border-border/50 shadow-md">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg font-semibold">사용자 현황</CardTitle>
          <Badge variant="secondary" className="gap-1 shadow-sm">
            <Users className="h-3 w-3" />
            {users.length}명
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        {users.map((user) => (
          <div
            key={user.id}
            className="flex items-center justify-between p-3 rounded-lg border border-border/70 hover:bg-muted/50 hover:border-border transition-all duration-200 shadow-sm"
          >
            <div className="flex items-center gap-3">
              <Avatar className="h-10 w-10">
                <AvatarFallback className="bg-primary/10 text-primary font-semibold">{user.name[0]}</AvatarFallback>
              </Avatar>

              <div>
                <div className="flex items-center gap-2">
                  <h4 className="font-semibold text-sm">{user.name}</h4>
                  {user.active && (
                    <div className="flex items-center gap-1">
                      <div className="h-2 w-2 rounded-full bg-chart-3 animate-pulse" />
                      <span className="text-xs text-chart-3 font-medium">온라인</span>
                    </div>
                  )}
                </div>
                <p className="text-xs text-muted-foreground">{user.email}</p>
              </div>
            </div>

            <Badge variant="outline" className="text-xs">
              {user.status}
            </Badge>
          </div>
        ))}
      </CardContent>
    </Card>
  )
}

import {
  Home,
  MessageSquare,
  UserCheck,
  Calendar,
  LayoutDashboard,
  Layers,
  Bell,
  Share2,
  Users,
  Archive,
  User,
  Settings,
  Activity,
  Menu,
} from "lucide-react"
import Link from "next/link"
import { cn } from "@/lib/utils"

const menuItems = [
  { icon: Home, label: "홈", active: true },
  { icon: MessageSquare, label: "문의" },
  { icon: UserCheck, label: "상담" },
  { icon: Calendar, label: "예약" },
  { icon: LayoutDashboard, label: "대시보드" },
  { icon: Layers, label: "채널 피커" },
  { icon: Bell, label: "테스트(레벨피칭)" },
  { icon: Share2, label: "채널" },
  { icon: Users, label: "채널 관리" },
  { icon: Archive, label: "휴지통" },
  { icon: User, label: "프로필" },
  { icon: Settings, label: "설정" },
  { icon: Activity, label: "활동기록" },
]

export function Sidebar() {
  return (
    <aside className="w-64 border-r border-sidebar-border bg-sidebar shadow-lg">
      <div className="flex h-16 items-center gap-2 border-b border-sidebar-border px-6">
        <Menu className="h-5 w-5 text-sidebar-foreground" />
        <span className="text-xl font-bold text-sidebar-foreground">MCRM</span>
      </div>

      <nav className="space-y-1 p-3">
        {menuItems.map((item) => {
          const Icon = item.icon
          return (
            <Link
              key={item.label}
              href="#"
              className={cn(
                "flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200",
                item.active
                  ? "bg-sidebar-accent text-sidebar-accent-foreground shadow-sm"
                  : "text-sidebar-foreground/80 hover:bg-sidebar-accent/70 hover:text-sidebar-foreground",
              )}
            >
              <Icon className="h-4 w-4" />
              {item.label}
            </Link>
          )
        })}
      </nav>
    </aside>
  )
}

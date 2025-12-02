import { DashboardHeader } from "@/components/dashboard-header"
import { StatsCards } from "@/components/stats-cards"
import { RecentInquiries } from "@/components/recent-inquiries"
import { UrgentConsultations } from "@/components/urgent-consultations"
import { InquiryDistribution } from "@/components/inquiry-distribution"
import { UserStatus } from "@/components/user-status"
import { Sidebar } from "@/components/sidebar"

export default function HomePage() {
  return (
    <div className="flex min-h-screen bg-muted/30">
      <Sidebar />

      <main className="flex-1 overflow-hidden">
        <DashboardHeader />

        <div className="p-8 space-y-6">
          <StatsCards />

          <div className="grid gap-6 lg:grid-cols-2">
            <RecentInquiries />
            <UrgentConsultations />
          </div>

          <div className="grid gap-6 lg:grid-cols-2">
            <InquiryDistribution />
            <UserStatus />
          </div>
        </div>
      </main>
    </div>
  )
}

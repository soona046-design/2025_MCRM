import * as React from 'react';
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  TableCaption,
  Badge,
} from 'insight-ui';

export const ChannelPerformanceTable = () => (
  <Table style={{ width: 640 }}>
    <TableCaption>2026년 7월 채널별 성과</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>채널</TableHead>
        <TableHead>리드 수</TableHead>
        <TableHead>상담 전환율</TableHead>
        <TableHead>광고비</TableHead>
        <TableHead>CPA</TableHead>
        <TableHead>상태</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow>
        <TableCell>네이버 검색광고</TableCell>
        <TableCell>86</TableCell>
        <TableCell>62%</TableCell>
        <TableCell>₩4,820,000</TableCell>
        <TableCell>₩56,046</TableCell>
        <TableCell><Badge>목표달성</Badge></TableCell>
      </TableRow>
      <TableRow>
        <TableCell>인스타그램</TableCell>
        <TableCell>41</TableCell>
        <TableCell>48%</TableCell>
        <TableCell>₩2,100,000</TableCell>
        <TableCell>₩51,220</TableCell>
        <TableCell><Badge variant="secondary">진행중</Badge></TableCell>
      </TableRow>
      <TableRow>
        <TableCell>홈페이지 문의</TableCell>
        <TableCell>19</TableCell>
        <TableCell>71%</TableCell>
        <TableCell>₩0</TableCell>
        <TableCell>-</TableCell>
        <TableCell><Badge variant="outline">자연유입</Badge></TableCell>
      </TableRow>
    </TableBody>
  </Table>
);

export const LeadListTable = () => (
  <Table style={{ width: 560 }}>
    <TableHeader>
      <TableRow>
        <TableHead>이름</TableHead>
        <TableHead>치료 유형</TableHead>
        <TableHead>유입 채널</TableHead>
        <TableHead>담당자</TableHead>
        <TableHead>상태</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow>
        <TableCell>김민지</TableCell>
        <TableCell>임플란트</TableCell>
        <TableCell>네이버</TableCell>
        <TableCell>김상담</TableCell>
        <TableCell><Badge variant="destructive">SLA 초과</Badge></TableCell>
      </TableRow>
      <TableRow>
        <TableCell>이서준</TableCell>
        <TableCell>교정</TableCell>
        <TableCell>인스타그램</TableCell>
        <TableCell>박매니저</TableCell>
        <TableCell><Badge>신규</Badge></TableCell>
      </TableRow>
      <TableRow>
        <TableCell>박하은</TableCell>
        <TableCell>보존치료</TableCell>
        <TableCell>네이버</TableCell>
        <TableCell>김상담</TableCell>
        <TableCell><Badge variant="secondary">상담중</Badge></TableCell>
      </TableRow>
    </TableBody>
  </Table>
);

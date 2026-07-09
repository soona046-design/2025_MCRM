import * as React from 'react';
import { useForm } from 'react-hook-form';
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormDescription,
  FormMessage,
  Input,
  Button,
} from 'insight-ui';

export const NewLeadForm = () => {
  const form = useForm({
    defaultValues: {
      name: '이서연',
      phone: '010-1234-5521',
      channel: '네이버 검색광고',
    },
  });

  return (
    <Form {...form}>
      <form style={{ display: 'grid', gap: 16, width: 360 }}>
        <FormField
          control={form.control}
          name="name"
          render={({ field }: any) => (
            <FormItem>
              <FormLabel>환자명</FormLabel>
              <FormControl>
                <Input {...field} />
              </FormControl>
              <FormDescription>상담 리스트에 표시되는 이름입니다.</FormDescription>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="phone"
          render={({ field }: any) => (
            <FormItem>
              <FormLabel>연락처</FormLabel>
              <FormControl>
                <Input {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="channel"
          render={({ field }: any) => (
            <FormItem>
              <FormLabel>유입 채널</FormLabel>
              <FormControl>
                <Input {...field} />
              </FormControl>
              <FormDescription>UTM 파라미터 기준으로 자동 채워집니다.</FormDescription>
              <FormMessage />
            </FormItem>
          )}
        />
        <Button type="submit">리드 저장</Button>
      </form>
    </Form>
  );
};

export const FormWithError = () => {
  const form = useForm({ defaultValues: { email: '' } });

  React.useEffect(() => {
    form.setError('email', { type: 'manual', message: '이메일 형식이 올바르지 않습니다.' });
  }, []);

  return (
    <Form {...form}>
      <form style={{ display: 'grid', gap: 16, width: 320 }}>
        <FormField
          control={form.control}
          name="email"
          render={({ field }: any) => (
            <FormItem>
              <FormLabel>담당자 이메일</FormLabel>
              <FormControl>
                <Input {...field} placeholder="agent@insight-mcrm.com" aria-invalid />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </form>
    </Form>
  );
};

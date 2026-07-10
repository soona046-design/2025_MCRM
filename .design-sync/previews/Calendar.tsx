import * as React from 'react';
import { Calendar } from 'insight-ui';

export const AppointmentDatePicker = () => (
  <Calendar
    mode="single"
    selected={new Date(2026, 6, 9)}
    defaultMonth={new Date(2026, 6, 1)}
  />
);

export const ConsultationRangePicker = () => (
  <Calendar
    mode="range"
    selected={{ from: new Date(2026, 6, 6), to: new Date(2026, 6, 10) }}
    defaultMonth={new Date(2026, 6, 1)}
  />
);

export function cuteDateTimePicker(config) {
    return {
        name: config.name,
        open: false,
        dropUp: false,
        alignRight: false,
        minDateStr: config.minDate || null,
        disablePast: config.disablePast !== false,

        // Selected date state
        selectedYear: null,
        selectedMonth: null,
        selectedDate: null,
        selectedHour: 0,
        selectedMinute: 0,

        // View navigation state
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),

        monthNames: [
            'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
            'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
            'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
        ],

        get minTime() {
            if (this.minDateStr) {
                const parsed = new Date(typeof this.minDateStr === 'string' ? this.minDateStr.replace(/-/g, '/') : this.minDateStr);
                if (!isNaN(parsed.getTime())) {
                    return new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate()).getTime();
                }
            }
            if (this.disablePast) {
                const now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
            }
            return 0;
        },

        get isPrevMonthDisabled() {
            if (!this.disablePast && !this.minDateStr) return false;
            const minD = new Date(this.minTime);
            return (this.viewYear < minD.getFullYear()) || 
                   (this.viewYear === minD.getFullYear() && this.viewMonth <= minD.getMonth());
        },

        init() {
            const now = new Date();
            let initial = now;

            if (config.initialValue) {
                const parsed = new Date(typeof config.initialValue === 'string' ? config.initialValue.replace(/-/g, '/') : config.initialValue);
                if (!isNaN(parsed.getTime())) {
                    initial = parsed;
                }
            }

            this.selectedYear = initial.getFullYear();
            this.selectedMonth = initial.getMonth();
            this.selectedDate = initial.getDate();
            this.selectedHour = initial.getHours();
            this.selectedMinute = initial.getMinutes();

            this.viewYear = this.selectedYear;
            this.viewMonth = this.selectedMonth;

            // Lắng nghe sự kiện cập nhật ngày từ bên ngoài (như nút preset +1, +7, +15 ngày)
            window.addEventListener(`set-datetime-${config.name}`, (e) => {
                if (e.detail) {
                    this.applyValue(e.detail);
                }
            });

            // Listen to window scroll & resize to update position dynamically
            window.addEventListener('resize', () => {
                if (this.open) this.calculatePosition();
            });
            window.addEventListener('scroll', () => {
                if (this.open) this.calculatePosition();
            }, true);
        },

        applyValue(val) {
            if (!val) return;
            const parsed = new Date(typeof val === 'string' ? val.replace(/-/g, '/') : val);
            if (!isNaN(parsed.getTime())) {
                this.selectedYear = parsed.getFullYear();
                this.selectedMonth = parsed.getMonth();
                this.selectedDate = parsed.getDate();
                this.selectedHour = parsed.getHours();
                this.selectedMinute = parsed.getMinutes();
                this.viewYear = this.selectedYear;
                this.viewMonth = this.selectedMonth;
            }
        },

        calculatePosition() {
            if (!this.$el) return;
            const rect = this.$el.getBoundingClientRect();
            const modalHeight = 390; // Chiều cao ước tính của khung lịch
            const modalWidth = 460;  // Chiều rộng ước tính của khung lịch
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;
            const windowWidth = window.innerWidth || document.documentElement.clientWidth;

            const spaceBelow = windowHeight - rect.bottom;
            const spaceAbove = rect.top;

            // Nếu phía dưới không đủ chỗ (dưới 390px) và phía trên còn nhiều chỗ hơn -> Hiển thị BÊN TRÊN
            if (spaceBelow < modalHeight && spaceAbove > spaceBelow) {
                this.dropUp = true;
            } else {
                this.dropUp = false;
            }

            // Nếu bên phải bị tràn mép màn hình -> Căn lề theo bên phải
            if (rect.left + modalWidth > windowWidth && rect.right - modalWidth >= 0) {
                this.alignRight = true;
            } else {
                this.alignRight = false;
            }
        },

        togglePicker() {
            this.open = !this.open;
            if (this.open) {
                this.calculatePosition();
                this.viewYear = this.selectedYear;
                this.viewMonth = this.selectedMonth;
                this.$nextTick(() => {
                    this.calculatePosition();
                    this.scrollToSelectedTime();
                });
            }
        },

        padZero(num) {
            return (num < 10 ? '0' : '') + num;
        },

        get formattedDisplayValue() {
            if (!this.selectedYear) return '';
            const d = this.padZero(this.selectedDate);
            const m = this.padZero(this.selectedMonth + 1);
            const y = this.selectedYear;
            const h = this.padZero(this.selectedHour);
            const min = this.padZero(this.selectedMinute);
            return `${d}/${m}/${y} - ${h}:${min}`;
        },

        get formattedRawValue() {
            if (!this.selectedYear) return '';
            const d = this.padZero(this.selectedDate);
            const m = this.padZero(this.selectedMonth + 1);
            const y = this.selectedYear;
            const h = this.padZero(this.selectedHour);
            const min = this.padZero(this.selectedMinute);
            return `${y}-${m}-${d} ${h}:${min}`;
        },

        get daysInGrid() {
            const year = this.viewYear;
            const month = this.viewMonth;

            const firstDayOfMonth = new Date(year, month, 1);
            const daysInCurrentMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            // Day of week: 0 is Sun, 1 is Mon... convert to Monday=0
            let startDay = firstDayOfMonth.getDay() - 1;
            if (startDay === -1) startDay = 6;

            const grid = [];
            const today = new Date();

            // Previous month days
            for (let i = startDay - 1; i >= 0; i--) {
                const d = daysInPrevMonth - i;
                const prevYear = month === 0 ? year - 1 : year;
                const prevMonth = month === 0 ? 11 : month - 1;
                const cellTime = new Date(prevYear, prevMonth, d).getTime();
                const isPast = cellTime < this.minTime;

                grid.push({
                    key: `prev-${d}`,
                    date: d,
                    month: prevMonth,
                    year: prevYear,
                    isOtherMonth: true,
                    isToday: false,
                    isSelected: false,
                    clickable: !isPast
                });
            }

            // Current month days
            for (let d = 1; d <= daysInCurrentMonth; d++) {
                const isToday = (
                    d === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear()
                );

                const isSelected = (
                    d === this.selectedDate &&
                    month === this.selectedMonth &&
                    year === this.selectedYear
                );

                const cellTime = new Date(year, month, d).getTime();
                const isPast = cellTime < this.minTime;

                grid.push({
                    key: `curr-${d}`,
                    date: d,
                    month: month,
                    year: year,
                    isOtherMonth: false,
                    isToday: isToday,
                    isSelected: isSelected,
                    clickable: !isPast
                });
            }

            // Next month days to fill 42 cells grid (6 weeks)
            const remaining = 42 - grid.length;
            for (let d = 1; d <= remaining; d++) {
                const nextYear = month === 11 ? year + 1 : year;
                const nextMonth = month === 11 ? 0 : month + 1;
                const cellTime = new Date(nextYear, nextMonth, d).getTime();
                const isPast = cellTime < this.minTime;

                grid.push({
                    key: `next-${d}`,
                    date: d,
                    month: nextMonth,
                    year: nextYear,
                    isOtherMonth: true,
                    isToday: false,
                    isSelected: false,
                    clickable: !isPast
                });
            }

            return grid;
        },

        prevMonth() {
            if (this.isPrevMonthDisabled) return;
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear--;
            } else {
                this.viewMonth--;
            }
        },

        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear++;
            } else {
                this.viewMonth++;
            }
        },

        selectDay(year, month, date) {
            const cellTime = new Date(year, month, date).getTime();
            if (cellTime < this.minTime) return;

            this.selectedYear = year;
            this.selectedMonth = month;
            this.selectedDate = date;
            this.viewYear = year;
            this.viewMonth = month;
            this.emitChange();
        },

        selectHour(h) {
            this.selectedHour = h;
            this.emitChange();
        },

        selectMinute(m) {
            this.selectedMinute = m;
            this.emitChange();
        },

        selectNow() {
            const now = new Date();
            this.selectedYear = now.getFullYear();
            this.selectedMonth = now.getMonth();
            this.selectedDate = now.getDate();
            this.selectedHour = now.getHours();
            this.selectedMinute = now.getMinutes();
            this.viewYear = this.selectedYear;
            this.viewMonth = this.selectedMonth;
            this.scrollToSelectedTime();
            this.emitChange();
        },

        confirmSelection() {
            this.open = false;
            this.emitChange();
        },

        scrollToSelectedTime() {
            if (this.$refs.hourList) {
                const selectedHBtn = this.$refs.hourList.children[this.selectedHour];
                if (selectedHBtn) {
                    this.$refs.hourList.scrollTop = selectedHBtn.offsetTop - this.$refs.hourList.offsetTop - 30;
                }
            }
            if (this.$refs.minuteList) {
                const selectedMBtn = this.$refs.minuteList.children[this.selectedMinute];
                if (selectedMBtn) {
                    this.$refs.minuteList.scrollTop = selectedMBtn.offsetTop - this.$refs.minuteList.offsetTop - 30;
                }
            }
        },

        emitChange() {
            this.$dispatch('input', this.formattedRawValue);
            this.$dispatch('change', this.formattedRawValue);
            window.dispatchEvent(new CustomEvent(`datetime-updated-${config.name}`, { detail: this.formattedRawValue }));
        }
    };
}

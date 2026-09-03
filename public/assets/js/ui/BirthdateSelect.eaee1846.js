export default class BirthdateSelect {
    constructor(day, month, year) {
        this.day = day;
        this.month = month;
        this.year = year;
        this.init();
    }

    init() {
        for (let i = 1; i <= 31; i++) {
            this.day.add(new Option(i, i));
        }

        [
            'Январь','Февраль','Март','Апрель','Май','Июнь',
            'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'
        ].forEach((m, i) => {
            this.month.add(new Option(m, i + 1));
        });

        const y = new Date().getFullYear();
        for (let i = y - 100; i <= y - 14; i++) {
            this.year.add(new Option(i, i));
        }
    }
}

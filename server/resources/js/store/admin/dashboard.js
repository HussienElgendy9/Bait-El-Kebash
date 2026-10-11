document.addEventListener('DOMContentLoaded', function () {

    console.log('Dashboard JS loaded');

    if (typeof Chart === 'undefined') {
        console.error('Chart.js did not load');
        return;
    }

    if (!window.dashboardData) {
        console.error('Dashboard data not found');
        return;
    }

    const {
        salesLabels,
        salesData,
        categoryLabels,
        categoryData
    } = window.dashboardData;


    /*
    |--------------------------------------------------------------------------
    | SALES CHART
    |--------------------------------------------------------------------------
    */

    const salesCanvas = document.getElementById('salesChart');

    if (salesCanvas) {

        new Chart(salesCanvas, {
            type: 'line',

            data: {
                labels: salesLabels,

                datasets: [
                    {
                        label: 'المبيعات',

                        data: salesData,

                        borderColor: '#004225',

                        backgroundColor: 'rgba(0, 66, 37, 0.08)',

                        borderWidth: 3,

                        fill: true,

                        tension: 0.4,

                        pointBackgroundColor: '#004225',

                        pointBorderColor: '#ffffff',

                        pointBorderWidth: 2,

                        pointRadius: 5,

                        pointHoverRadius: 7,
                    }
                ]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false,
                },

                plugins: {

                    legend: {
                        display: false,
                    },

                    tooltip: {

                        rtl: true,

                        callbacks: {
                            label: function (context) {
                                return 'المبيعات: ' +
                                    Number(context.parsed.y).toLocaleString('ar-EG') +
                                    ' جنيه';
                            }
                        }
                    }
                },

                scales: {

                    x: {

                        grid: {
                            display: false,
                        },

                        ticks: {
                            color: '#6b7280',
                            font: {
                                family: 'Arial',
                                weight: 'bold',
                            }
                        }
                    },

                    y: {

                        beginAtZero: true,

                        border: {
                            display: false,
                        },

                        grid: {
                            color: 'rgba(0, 66, 37, 0.08)',
                        },

                        ticks: {

                            color: '#6b7280',

                            callback: function (value) {
                                return Number(value).toLocaleString('ar-EG') + ' ج';
                            }
                        }
                    }
                }
            }
        });

    } else {
        console.error('salesChart canvas not found');
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY CHART
    |--------------------------------------------------------------------------
    */

    const categoryCanvas = document.getElementById('categoryChart');

    if (categoryCanvas) {

        new Chart(categoryCanvas, {
            type: 'doughnut',

            data: {
                labels: categoryLabels,

                datasets: [
                    {
                        data: categoryData,

                        backgroundColor: [
                            '#004225',
                            '#8b5e3c',
                            '#98d4ac',
                            '#f4bb92',
                            '#002a15',
                            '#ece2c7',
                        ],

                        borderColor: '#ece2c7',

                        borderWidth: 5,

                        hoverOffset: 12,
                    }
                ]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {
                            padding: 18,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: 'Arial',
                                weight: 'bold',
                            }
                        }
                    },

                    tooltip: {

                        rtl: true,

                        callbacks: {

                            label: function (context) {

                                return (
                                    context.label +
                                    ': ' +
                                    context.parsed +
                                    ' منتج'
                                );
                            }
                        }
                    }
                }
            }
        });

    } else {
        console.error('categoryChart canvas not found');
    }

});
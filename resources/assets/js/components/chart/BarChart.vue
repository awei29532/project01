<script>
import { Bar, mixins } from "vue-chartjs";
const { reactiveProp } = mixins;

export default {
    mixins: [reactiveProp],
    extends: Bar,
    data: () => ({
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    }),
    mounted() {
        this.renderChart(this.chartData, this.options);
    },
};
</script>
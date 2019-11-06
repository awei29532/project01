<script>
import { Line, mixins } from "vue-chartjs";
const { reactiveProp } = mixins;

export default {
    mixins: [reactiveProp],
    extends: Line,
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
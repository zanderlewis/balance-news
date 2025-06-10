<div>
    <x-base-news 
        :articles="$articles"
        :biasOptions="$biasOptions"
        :sourceOptions="$sourceOptions"
        :timeRangeOptions="$timeRangeOptions"
        :biasDistribution="$biasDistribution"
        :search="$search"
        :selectedBias="$selectedBias"
        :selectedSource="$selectedSource"
        :selectedTimeRange="$selectedTimeRange"
        :showHeader="false"
        headerTitle="Latest News Stories"
        headerSubtitle="Search and filter articles by bias, source, and time range"
    />
</div>

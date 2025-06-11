<div>
    <x-base-news 
        :articles="$articles"
        :biasOptions="$biasOptions"
        :sourceOptions="$sourceOptions"
        :timeRangeOptions="$timeRangeOptions"
        :biasDistribution="$biasDistribution"
        :bookmarkedIds="$bookmarkedIds"
        :search="$search"
        :selectedBias="$selectedBias"
        :selectedSource="$selectedSource"
        :selectedTimeRange="$selectedTimeRange"
        :showHeader="true"
        headerTitle="Your Bookmarked Articles"
        headerSubtitle="Manage and explore your saved articles"
    />
</div>

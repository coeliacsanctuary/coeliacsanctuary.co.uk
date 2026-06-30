<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Ai\Agents\PrepareRecommendedEatery;
use App\DataObjects\EatingOut\AiPreparedRecommendation;
use App\Models\EatingOut\EateryRecommendation;

class SendEateryRecommendationToAiAction
{
    public function handle(EateryRecommendation $eateryRecommendation): AiPreparedRecommendation
    {
        $prompt = view('prompts.prepare-recommended-eatery-prompt', [
            'recommendation' => $eateryRecommendation,
        ])->render();

        $info = PrepareRecommendedEatery::make()->prompt($prompt);

        //        $exampleResponse = '{"data":{"place_name":"Beach Box Cafe","place_address":"Harlyn Beach Car Park\nHarlyn Bay Road\nHarlyn Bay\nPadstow\nCornwall\nPL28 8SB","place_country":"England","place_county":"Cornwall","place_town":"Padstow","place_area":null,"latitude":50.5401661,"longitude":-4.9935793,"phone_number":"01208 640564","website":"https://www.beachboxcornwall.co.uk/our-cafes/harlyn-bay/","facebook":"https://www.facebook.com/BeachBoxCornwall/","instagram":"https://www.instagram.com/beachboxcornwall/","eatery_Type":"Eatery","venue_type":"Cafe","cuisine":"English","info":"Sat right by the golden sands of Harlyn Bay, Beach Box Cafe is a relaxed beach-side spot offering stunning views of the bay from their decking. Their on-site kitchen serves up pizzas, burgers, chips and breakfast baps, along with cinnamon buns from Da Bara Bakery, flat whites and a selection of cakes. Gluten free options are available, including pizza, though as they only have one pizza oven they cannot guarantee against cross contamination, so it may not suit the most sensitive coeliacs. The cafe is licensed, dog-friendly all year round and open every season, with an Airstream Bar across the decking serving beers, wines and cocktails through the peak months.","features":["Pizza","Parking"]},"explanation":"Confirmed the cafe\'s full address from the Food Standards Agency listing and a public licensing notice: Harlyn Beach Car Park, Harlyn Bay Road, Harlyn Bay, Padstow, PL28 8SB. Phone number 01208 640564 is taken from the official website footer, along with Facebook and Instagram links. Coordinates obtained via GeoLookup. The cafe is in Cornwall, England, town Padstow (confirmed in our database). It\'s a beachside cafe serving pizza, burgers, breakfast items, cakes and coffee — best fit is venue type \"Cafe\" and cuisine \"English\". Features include Pizza (they serve gluten free pizza) and Parking (it\'s located in the beach car park). I have not marked it as \"Gluten Free Menu\" or \"100% Gluten Free\" since the website lists gluten free as an option/icon but the recommender notes they cannot guarantee against cross contamination (one pizza oven). The info field is written factually based on the official website and the recommender\'s notes, in a style matching typical SEO-friendly listing copy, including the caveat about cross contamination to help coeliac visitors make an informed choice.", "is_eligible": true}';
        //        $info = (object) ['text' => $exampleResponse];

        $aiInfo = json_decode($info->text, true);

        return AiPreparedRecommendation::fromArray($aiInfo);
    }
}

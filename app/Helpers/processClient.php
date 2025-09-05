<?php
namespace App\Helpers;

use App\Models\sales\SalesClients;
use Illuminate\Support\Str;

class processClient{

    public static function createClient($request)
    {
        $client = SalesClients::where('email',$request->client['email'])->first();
        if (!empty($client)){
            $firstName =  $request->client['first_name'] != null ? $request->client['first_name'] : $client->first_name;
            $lastName = $request->client['last_name'] != null ? $request->client['last_name'] : $client->last_name;

            $client->fill([
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'name'          => $firstName. ' '. $lastName,
                'phone'         => !empty($request->client['phone']) ? $request->client['phone'] : $client->phone,
                'location'      => !empty($request->client['location']) ? $request->client['location'] : $client->location,
                'identifier'      => !empty($request->client['identifier']) ? $request->client['identifier'] : $client->identifier,
                'device_id'      => $request->deviceFinger,
            ]);
            $client->save();

        }
        else
        {
            $client = SalesClients::create([
                'uuid'          =>  str::uuid(),
                'first_name'    => $request->client['first_name'],
                'last_name'     => $request->client['last_name'],
                'name'          => $request->client['first_name']. ' '. $request->client['last_name'],
                'phone'         => !empty($request->client['phone']) ? $request->client['phone'] : '',
                'email'         => !empty($request->client['email']) ? $request->client['email'] : '',
                'identifier'      => !empty($request->client['identifier']) ? $request->client['identifier'] : '',
                'device_id'      => $request->deviceFinger,
            ]);
        }
        return $client;

    }

}

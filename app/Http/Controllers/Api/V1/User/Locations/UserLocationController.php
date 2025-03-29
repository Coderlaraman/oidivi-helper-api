<?php

namespace App\Http\Controllers\Api\V1\User\Locations;

use App\Events\LocationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientLocationResource;
use App\Models\Location;
use App\Models\LocationHistory;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserLocationController extends Controller
{
    use ApiResponseTrait;

    public function updateLocation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'service_request_id' => 'nullable|exists:service_requests,id'
            ]);

            $userId = auth()->id();

            // Actualizar ubicación actual
            $location = Location::updateOrCreate(
                ['user_id' => $userId],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude']
                ]
            );

            // Guardar en historial si hay service_request_id
            if (isset($validated['service_request_id'])) {
                LocationHistory::create([
                    'user_id' => $userId,
                    'service_request_id' => $validated['service_request_id'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude']
                ]);
            }

            broadcast(new LocationUpdated($location));

            return $this->successResponse(
                new ClientLocationResource($location),
                'Ubicación actualizada exitosamente'
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Error de validación',
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al actualizar ubicación',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}



// Implementación en el frontrend

// <template>
//   <div id="map"></div>
// </template>

// <script>
// import Echo from "laravel-echo";
// import Pusher from "pusher-js";
// import axios from "axios";

// export default {
//   data() {
//     return {
//       map: null,
//       marker: null,
//       polyline: null, // Línea de ruta
//       userId: 1, // 🔹 Se debe obtener dinámicamente según el usuario autenticado
//       serviceRequestId: null, // ID de la solicitud activa
//     };
//   },
//   mounted() {
//     this.initMap();
//     this.listenForUpdates();

//     // Si el usuario tiene una solicitud activa, obtener el historial
//     if (this.serviceRequestId) {
//       this.loadLocationHistory();
//     }
//   },
//   methods: {
//     initMap() {
//       this.map = new google.maps.Map(document.getElementById("map"), {
//         center: { lat: 0, lng: 0 },
//         zoom: 15
//       });
//       this.marker = new google.maps.Marker({ map: this.map });

//       // 🔹 Inicializar la línea de ruta en el mapa
//       this.polyline = new google.maps.Polyline({
//         path: [],
//         geodesic: true,
//         strokeColor: "#FF0000",
//         strokeOpacity: 1.0,
//         strokeWeight: 2
//       });
//       this.polyline.setMap(this.map);
//     },
//     listenForUpdates() {
//       Echo.channel("location-tracking." + this.userId).listen("LocationUpdated", (event) => {
//         const { latitude, longitude } = event.location;
//         const newPosition = new google.maps.LatLng(latitude, longitude);

//         this.marker.setPosition(newPosition);
//         this.map.setCenter(newPosition);

//         // 🔹 Si hay una solicitud activa, agregar punto a la ruta
//         if (this.serviceRequestId) {
//           const path = this.polyline.getPath();
//           path.push(newPosition);
//         }
//       });
//     },
//     async loadLocationHistory() {
//       try {
//         const response = await axios.get(`/api/location-history/${this.serviceRequestId}`);
//         const history = response.data.locations;

//         // 🔹 Dibujar la ruta del historial en el mapa
//         const path = this.polyline.getPath();
//         history.forEach((loc) => {
//           path.push(new google.maps.LatLng(loc.latitude, loc.longitude));
//         });
//       } catch (error) {
//         console.error("Error al cargar historial de ubicaciones:", error);
//       }
//     }
//   }
// };
// </script>



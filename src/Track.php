<?php
/**
 * Track.php
 *
 * @author huangbinbin
 * @date   2023/9/12 9:50
 */

namespace Crasp\Seventeentrack;


use Crasp\Seventeentrack\Exceptions\CallErrorException;
use Crasp\Seventeentrack\Exceptions\InvailArgumentException;
use Crasp\Seventeentrack\Traits\HasHttpRequest;

class Track
{
    use HasHttpRequest;

    const API_VERSION = '/v2.1';

    //注册运单
    const REGISTER_URI = '/register';

    //修改运输商
    const CHANGE_CARRIER_URI = '/changecarrier';

    //停止追踪
    const STOP_TRACK_URI = '/stoptrack';

    //重新追踪
    const RE_TRACK_URI = '/retrack';

    //获取物流信息
    const GET_TRACK_INFO_URI = '/gettrackinfo';

    //修改物流信息
    const CHANGE_INFO = '/changeinfo';

    //删除物流
    const DELETE_TRACK = '/deletetrack';

    //手动推送
    const SELF_PUSH = '/push';

    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * TrackingConnector constructor.
     *
     * @param string      $apiKey
     * @param string|null $host
     */
    public function __construct(string $apiKey, string $host = null)
    {
        $this->config = new Config($apiKey, $host);
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 注册运单号
     *
     * @param string      $trackNumber
     * @param string|null $carrier
     * @param array       $params
     *
     * @return bool
     * @throws CallErrorException|InvailArgumentException
     */
    public function register(string $trackNumber, string $carrier = null, array $params = [])
    {
        $registerParams = ['number' => $trackNumber];
        if (!empty($carrier)) {
            $registerParams['carrier'] = $carrier;
        }
        $registerParams = array_merge($registerParams, $params);
        $response = $this->registerMulti([
            $registerParams,
        ]);

        return $response;
    }

    /**
     * 批量注册运单号
     *
     * @param array $params
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function registerMulti(array $params): array
    {
        $this->checkCount($params);

        $url = $this->config->getHost() . self::API_VERSION . self::REGISTER_URI;

        return $this->baseRequest($params, $url);
    }

    /**
     * 获取单条物流信息
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     *
     * @return array
     * @throws CallErrorException
     */
    public function getTrackInfo(string $trackNumber, int $carrier = null): array
    {
        $params['number'] = $trackNumber;
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        $trackInfo = $this->getTrackInfoMulti([
            $params,
        ]);


        return $trackInfo['data']['accepted'][0];
    }

    /**
     * 获取多条条物流信息
     *
     * @param array $trackInfos
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function getMutiTrackInfo(array $trackInfos): array
    {
        $this->checkCount($trackInfos);

        $trackInfo = $this->getTrackInfoMulti($trackInfos);


        return $trackInfo['data']['accepted'];
    }

    /**
     * @param array $trackNumbers
     *
     * @return array
     * @throws CallErrorException
     */
    public function getTrackInfoMulti(array $trackNumbers): array
    {
        $url = $this->config->getHost() . self::API_VERSION . self::GET_TRACK_INFO_URI;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * 修改运输商
     *
     * @param string   $trackNumber
     * @param int      $carrierNew
     * @param int|null $carrierOld
     * @param array    $params
     *
     * @return mixed
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function changeCarrier(string $trackNumber, int $carrierNew, int $carrierOld = null, array $params = []): mixed
    {
        $changeParams = [
            'number'      => $trackNumber,
            'carrier_new' => $carrierNew,
        ];
        if (!empty($carrierOld)) {
            $params['carrier_old'] = $carrierOld;
        }
        return $this->changeCarrierMulti([array_merge($changeParams, $params)]);
    }

    /**
     * @param array $params
     * @return array|mixed
     * @throws CallErrorException
     * @throws InvailArgumentException
     * @author huangbinbin
     * @date   2023/9/12 14:20
     */
    public function changeCarrierMulti(array $params = []): mixed
    {
        $this->checkCount($params);
        $url = $this->config->getHost() . self::API_VERSION . self::CHANGE_CARRIER_URI;

        return $this->baseRequest($params, $url);
    }

    /**
     * 停止跟踪
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     *
     */
    public function stopTracking(string $trackNumber, int $carrier = null): array
    {
        $params['number'] = $trackNumber;
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        return $this->stopTrackingMulti([
            $params,
        ]);
    }

    /**
     * 批量停止跟踪
     *
     * @param array $trackNumbers
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function stopTrackingMulti(array $trackNumbers): array
    {
        $this->checkCount($trackNumbers);
        $url = $this->config->getHost() . self::API_VERSION . self::STOP_TRACK_URI;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * 重启跟踪
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     *
     */
    public function reTrack(string $trackNumber, int $carrier = null): array
    {
        $params['number'] = $trackNumber;
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        return $this->reTrackMulti([
            $params,
        ]);

    }

    /**
     * 批量重启跟踪
     *
     * @param array $trackNumbers
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function reTrackMulti(array $trackNumbers): array
    {
        $this->checkCount($trackNumbers);

        $url = $this->config->getHost() . self::API_VERSION . self::RE_TRACK_URI;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * 修改物流单号
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     * @param array    $items
     *
     * @return mixed
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function changeTrack(string $trackNumber, int $carrier = null, array $items = []): mixed
    {
        $params = [
            'number' => $trackNumber,
            'items'  => $items,
        ];
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        return $this->changeTrackMulti([
            $params,
        ]);

    }

    /**
     * 批量修改物流单号
     *
     * @param array $trackNumbers
     *
     * @return mixed
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function changeTrackMulti(array $trackNumbers): mixed
    {
        $this->checkCount($trackNumbers);

        $url = $this->config->getHost() . self::API_VERSION . self::CHANGE_INFO;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * 删除物流单号
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function delTrack(string $trackNumber, int $carrier = null): array
    {
        $params = [
            'number' => $trackNumber,
        ];
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        return $this->delTrackMulti([
            $params,
        ]);

    }

    /**
     * 批量删除物流单号
     *
     * @param array $trackNumbers
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function delTrackMulti(array $trackNumbers): array
    {
        $this->checkCount($trackNumbers);

        $url = $this->config->getHost() . self::API_VERSION . self::DELETE_TRACK;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * 手动推送物流单号
     *
     * @param string   $trackNumber
     * @param int|null $carrier
     *
     * @return array
     */
    public function selfPush(string $trackNumber, int $carrier = null): array
    {
        $params = [
            'number' => $trackNumber,
        ];
        if (!empty($carrier)) {
            $params['carrier'] = $carrier;
        }
        return $this->selfPushMulti([
            $params,
        ]);

    }

    /**
     * 批量手动推送物流单号
     *
     * @param array $trackNumbers
     *
     * @return array
     * @throws CallErrorException
     * @throws InvailArgumentException
     */
    public function selfPushMulti(array $trackNumbers): array
    {
        $this->checkCount($trackNumbers);

        $url = $this->config->getHost() . self::API_VERSION . self::SELF_PUSH;

        return $this->baseRequest($trackNumbers, $url);
    }

    /**
     * @param array  $body
     * @param string $url
     *
     * @return mixed
     * @throws CallErrorException
     */
    protected function baseRequest(array $body, string $url): array
    {
        try {
            return $this->postJson($url, $body, $this->config->getHeaders());
        } catch (\Exception $exception) {
            throw new CallErrorException($exception->getMessage());
        }
    }

    /**
     * 批量提交的数量检查
     *
     * @param array $params
     *
     * @throws InvailArgumentException
     * @author huangbinbin
     * @date   2023/9/12 11:04
     */
    protected function checkCount(array $params)
    {
        if (count($params) > 40) {
            throw  new InvailArgumentException('单次可提交数量不能大于40');
        }
    }
}

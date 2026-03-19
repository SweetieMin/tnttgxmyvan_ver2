<?php

namespace App\Foundation;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class BibleVerseKids
{
    /**
     * Lấy ngẫu nhiên một câu Kinh Thánh dành cho thiếu nhi.
     *
     * @return string
     */
    public static function verse()
    {
        return static::formatForConsole(static::verses()->random());
    }

    /**
     * Danh sách các câu Kinh Thánh dành cho thiếu nhi.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function verses()
    {
        return new Collection([
            'Hãy để trẻ nhỏ đến cùng Thầy, đừng ngăn cản chúng. - Mt 19:14',
            'Hãy yêu thương nhau như Thầy đã yêu thương anh em. - Ga 15:12',
            'Chúa là mục tử chăn dắt tôi, tôi chẳng thiếu thốn gì. - Tv 23:1',
            'Đừng sợ, vì Ta ở với con. - Is 41:10',
            'Anh em là ánh sáng cho trần gian. - Mt 5:14',
            'Tình yêu thương không bao giờ mất. - 1Cr 13:8',
            'Hãy làm cho người khác điều mình muốn họ làm cho mình. - Mt 7:12',
            'Hỡi con, hãy vâng lời cha mẹ trong Chúa, vì đó là điều phải lẽ. - Ep 6:1',
            'Hãy vui mừng luôn mãi. - 1Tx 5:16',
            'Hãy tạ ơn trong mọi hoàn cảnh. - 1Tx 5:18',
            'Ta đến để cho chiên được sống và sống dồi dào. - Ga 10:10',
            'Thiên Chúa là tình yêu. - 1Ga 4:8',
            'Phúc thay ai có tâm hồn nghèo khó, vì Nước Trời là của họ. - Mt 5:3',
            'Mọi sự anh em làm, hãy làm vì vinh quang Thiên Chúa. - 1Cr 10:31',
            'Đừng để điều ác thắng được con, nhưng hãy lấy điều thiện mà thắng điều ác. - Rm 12:21',
            'Hãy vui mừng trong niềm hy vọng, kiên nhẫn trong gian truân, trung thành trong cầu nguyện. - Rm 12:12',
            'Ai hạ mình xuống như trẻ nhỏ này, người ấy là kẻ lớn nhất trong Nước Trời. - Mt 18:4',
            'Hãy yêu mến Chúa hết lòng, hết linh hồn và hết trí khôn. - Mt 22:37',
            'Hãy cầu nguyện không ngừng. - 1Tx 5:17',
            'Chúa là nơi nương náu và sức mạnh của chúng ta. - Tv 46:1',
            'Hãy tin tưởng vào Chúa hết lòng, chớ cậy vào sự hiểu biết của con. - Cn 3:5',
            'Mọi sự đều có thể đối với người tin. - Mc 9:23',
            'Ngọn đèn của con là Lời Chúa. - Tv 119:105',
            'Phúc thay ai hiền lành, vì họ sẽ được Đất Hứa làm gia nghiệp. - Mt 5:5',
            'Hãy làm tất cả với tình yêu. - 1Cr 16:14',
            'Ai trung tín trong việc nhỏ, cũng sẽ trung tín trong việc lớn. - Lc 16:10',
            'Hãy luôn sẵn sàng làm điều tốt. - Tt 3:1',
            'Chúa yêu thương kẻ vui lòng cho đi. - 2Cr 9:7',
            'Hãy bước đi trong tình yêu, như Đức Kitô đã yêu thương anh em. - Ep 5:2',
            'Hãy để bình an của Đức Kitô ngự trị trong lòng anh em. - Cl 3:15',
        ]);
    }

    /**
     * Định dạng đẹp cho console hoặc hiển thị text.
     *
     * @param  string  $verse
     * @return string
     */
    protected static function formatForConsole($verse)
    {
        [$text, $ref] = (new Stringable($verse))->explode('-');

        return sprintf(
            "\n  <options=bold>“ %s ”</>\n  <fg=gray>— %s</>\n",
            trim($text),
            trim($ref),
        );
    }
}
